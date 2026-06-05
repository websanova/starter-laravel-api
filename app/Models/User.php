<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Role;
use App\Enums\StoragePath;
use App\Models\Concerns\HasTrashedScope;
use App\Models\Concerns\Searchable;
use App\Notifications\ResetPasswordNotification;
use App\Observers\SearchableObserver;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Format;
use Intervention\Image\Laravel\Facades\Image;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[ObservedBy(SearchableObserver::class)]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, HasTrashedScope, Notifiable, Searchable, SoftDeletes;

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
        'email',
        'email_verified_at',
        'password',
        'avatar',
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
            'last_active_at' => 'datetime',
            'password' => 'hashed',
            'is_password_reset_required' => 'boolean',
        ];
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
     * Get the full URL of the user's avatar.
     */
    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->avatar
                ? Storage::disk('s3')->url($this->avatar)
                : null,
        );
    }

    /**
     * Filter by role.
     */
    public function scopeForRole(Builder $query, ?Role $role): void
    {
        if (is_null($role)) {
            return;
        }

        $query->role($role->value);
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

        Storage::disk('s3')->put($path, (string) $image);
        $this->update(['avatar' => $path]);

        return $path;
    }

    /**
     * Delete the user's avatar from storage.
     */
    public function deleteAvatar(): void
    {
        if ($this->avatar) {
            Storage::disk('s3')->delete($this->avatar);
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
