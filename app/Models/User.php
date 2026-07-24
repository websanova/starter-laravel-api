<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\SortDirection;
use App\Enums\StoragePath;
use App\Enums\UserRole;
use App\Enums\UserSort;
use App\Models\Concerns\HasTrashedScope;
use App\Models\Concerns\ManagesSubscription;
use App\Models\Concerns\ManagesVerification;
use App\Models\Concerns\Searchable;
use App\Notifications\ResetPasswordNotification;
use App\Observers\SearchableObserver;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Format;
use Intervention\Image\Laravel\Facades\Image;
use Laravel\Cashier\Billable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[ObservedBy(SearchableObserver::class)]
class User extends Authenticatable implements HasLocalePreference
{
    /** @use HasFactory<UserFactory> */
    use Billable, HasApiTokens, HasFactory, HasRoles, HasTrashedScope, ManagesSubscription, ManagesVerification, Notifiable, Searchable, SoftDeletes;

    protected $guard_name = 'api';

    /**
     * The fields that feed into the keywords column for fulltext search.
     *
     * @var list<string>
     */
    protected array $searchable = [
        'first_name',
        'last_name',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'locale',
        'timezone',
        'email',
        'email_verified_at',
        'phone',
        'phone_verified_at',
        'password',
        'avatar',
        'plan_id',
        'keywords',
        'last_active_at',
        'is_password_reset_required',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'last_active_at' => 'datetime',
            'password' => 'hashed',
            'is_password_reset_required' => 'boolean',
        ];
    }

    /**
     * Get the user's plan, falling back to the free plan.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class)->withDefault(function () {
            return Plan::free();
        });
    }

    /**
     * Get the user's categories.
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Get the user's bookmarks.
     */
    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    /**
     * Get the user's tags.
     */
    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }

    /**
     * Get the user's notifications.
     */
    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable')->latest();
    }

    /**
     * Get the user's locale, falling back to the configured default.
     */
    protected function locale(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ?? config('user.default_locale'),
        );
    }

    /**
     * Get the user's timezone, falling back to the configured default.
     */
    protected function timezone(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ?? config('user.default_timezone'),
        );
    }

    /**
     * Get the full URL of the user's avatar.
     */
    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->avatar
                ? Storage::url($this->avatar)
                : null,
        );
    }

    /**
     * Sort by the given column and direction.
     */
    public function scopeSortBy(Builder $query, ?UserSort $column = null, ?SortDirection $direction = null): void
    {
        $col = $column ?? UserSort::CreatedAt;
        $dir = $direction ?? SortDirection::Desc;

        if ($col === UserSort::Name) {
            $query->orderBy('first_name', $dir->value)
                ->orderBy('last_name', $dir->value);
        } else {
            $query->orderBy($col->value, $dir->value);
        }
    }

    /**
     * Filter by role.
     */
    public function scopeForRole(Builder $query, ?array $roles): void
    {
        if (is_null($roles)) {
            return;
        }

        $query->role(array_map(fn (UserRole $role) => $role->value, $roles));
    }

    /**
     * Search keywords and email.
     */
    public function scopeForSearch(Builder $query, ?string $term): void
    {
        if (is_null($term) || trim($term) === '') {
            return;
        }

        $query->where(function ($q) use ($term) {
            $q->forKeywordsSearch($term)
                ->orWhere('email', 'like', "%{$term}%");
        });
    }

    /**
     * Resolve the user's stored locale to a locale the application has
     * translations for. The stored value is a BCP 47 tag (hyphenated) while
     * translation folders use the underscore convention, so the tag is
     * normalized first, then matched most specific to least: exact locale,
     * base language, then the configured fallback.
     */
    public function preferredLocale(): string
    {
        $supported = config('app.supported_locales');
        $locale = str_replace('-', '_', $this->locale);

        if (in_array($locale, $supported)) {
            return $locale;
        }

        $base = Str::before($locale, '_');

        if (in_array($base, $supported)) {
            return $base;
        }

        return config('app.fallback_locale');
    }

    /**
     * Send the password reset notification.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Store a new avatar, replacing any existing one.
     */
    public function storeAvatar(UploadedFile $file): string
    {
        $this->deleteAvatar();

        $image = Image::decode($file)
            ->coverDown(200, 200)
            ->encodeUsingFormat(Format::PNG);

        $path = StoragePath::UserAvatar->value . '/' . Str::random(40) . '.png';

        Storage::put($path, (string) $image);
        $this->update(['avatar' => $path]);

        return $path;
    }

    /**
     * Delete the user's avatar from storage.
     */
    public function deleteAvatar(): void
    {
        if ($this->avatar) {
            Storage::delete($this->avatar);
            $this->update(['avatar' => null]);
        }
    }

    /**
     * Clean up all related data and hard delete the user.
     */
    public function purge(): void
    {
        $this->deleteAvatar();
        $this->tokens()->delete();
        $this->forceDelete();
    }

    /**
     * Strip personally identifiable information but keep the row.
     */
    public function anonymize(): void
    {
        $this->deleteAvatar();
        $this->tokens()->delete();

        $this->forceFill([
            'first_name' => 'Deleted',
            'last_name' => 'User',
            'email' => 'deleted_' . Str::random(32) . '@anonymized.local',
            'password' => Str::random(64),
            'remember_token' => null,
        ])->save();
    }
}
