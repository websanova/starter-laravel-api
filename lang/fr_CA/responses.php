<?php

return [

    'auth.failed' => 'Les informations d identification fournies sont incorrectes.',
    'auth.unverified' => 'Votre compte n est pas verifie.',
    'auth.throttle' => 'Trop de tentatives de connexion. Veuillez reessayer dans :seconds secondes.',
    'auth.deleted' => 'Ce compte a ete supprime.',
    'auth.password_reset_required' => 'Vous devez mettre a jour votre mot de passe avant de continuer.',
    'auth.forbidden' => 'Vous n avez pas la permission d acceder a cette ressource.',

    'passwords.reset' => 'Votre mot de passe a ete reinitialise.',
    'passwords.sent' => 'Nous vous avons envoye par courriel votre lien de reinitialisation de mot de passe.',
    'passwords.sent_if_exists' => 'Si ce courriel est enregistre, un lien de reinitialisation de mot de passe a ete envoye.',
    'passwords.throttled' => 'Veuillez patienter avant de reessayer.',
    'passwords.token' => 'Ce jeton de reinitialisation de mot de passe est invalide.',
    'passwords.user' => 'Aucun compte n a ete trouve avec cette adresse courriel.',

    'password.updated' => 'Votre mot de passe a ete mis a jour.',

    'verification.verified' => 'Verifie avec succes.',
    'verification.sent' => 'Code de verification envoye.',
    'verification.throttled' => 'Veuillez patienter avant de demander un nouveau code.',
    'verification.invalid_code' => 'Le code de verification est incorrect.',
    'verification.no_valid_code' => 'Aucun code de verification valide trouve. Veuillez en demander un nouveau.',
    'verification.channel_disabled' => 'La verification n\'est pas disponible pour ce canal.',
    'verification.channel_unavailable' => 'Il n\'y a rien ou envoyer un code pour ce canal.',

    'email_change.sent' => 'Un lien de confirmation a ete envoye a votre nouvelle adresse courriel.',
    'email_change.confirmed' => 'Votre adresse courriel a ete mise a jour.',
    'email_change.invalid_token' => 'Ce jeton de changement de courriel est invalide ou a expire.',
    'email_change.throttled' => 'Veuillez patienter avant de demander un autre changement de courriel.',

    'avatar.stored' => 'Avatar televerse avec succes.',
    'avatar.destroyed' => 'Avatar supprime avec succes.',

    'admin.user.updated' => 'Utilisateur mis a jour avec succes.',
    'admin.user.deleted' => 'Utilisateur supprime avec succes.',
    'admin.user.force_deleted' => 'Utilisateur supprime definitivement.',
    'admin.user.restored' => 'Utilisateur restaure avec succes.',
    'admin.user.avatar_destroyed' => 'Avatar de l utilisateur supprime avec succes.',
    'admin.user.role_updated' => 'Role de l utilisateur mis a jour avec succes.',
    'admin.user.password_reset' => 'Un mot de passe temporaire a ete envoye a l utilisateur.',
    'admin.user.subscription_created' => 'Abonnement de l utilisateur cree avec succes.',
    'admin.user.subscription_cancelled' => 'Abonnement de l utilisateur annule avec succes.',
    'admin.user.subscription_resumed' => 'Abonnement de l utilisateur repris avec succes.',
    'admin.user.subscription_coupon_applied' => 'Coupon applique a l abonnement de l utilisateur avec succes.',
    'admin.user.subscription_coupon_removed' => 'Coupon retire de l abonnement de l utilisateur avec succes.',

    'category.created' => 'Categorie creee avec succes.',
    'category.updated' => 'Categorie mise a jour avec succes.',
    'category.deleted' => 'Categorie supprimee avec succes.',

    'tag.created' => 'Etiquette creee avec succes.',
    'tag.updated' => 'Etiquette mise a jour avec succes.',
    'tag.deleted' => 'Etiquette supprimee avec succes.',

    'bookmark.created' => 'Signet cree avec succes.',
    'bookmark.updated' => 'Signet mis a jour avec succes.',
    'bookmark.deleted' => 'Signet supprime avec succes.',

    'billing.address_invalid' => 'Cette adresse n a pas pu etre verifiee. Veuillez la corriger et reessayer.',
    'billing.provider_unavailable' => 'Le fournisseur de facturation n a pas pu etre joint. Veuillez reessayer.',

    'subscription.required' => 'Un abonnement actif est requis pour acceder a cette ressource.',
    'subscription.created' => 'Abonnement cree avec succes.',
    'subscription.updated' => 'Abonnement mis a jour avec succes.',
    'subscription.cancelled' => 'Abonnement annule avec succes.',
    'subscription.resumed' => 'Abonnement repris avec succes.',
    'subscription.payment_method_required' => 'Un mode de paiement est requis avant de vous abonner.',
    'subscription.payment_failed' => 'Votre mode de paiement a ete refuse. Mettez le a jour et reessayez.',
    'subscription.nothing_to_sync' => 'Aucun paiement complete a appliquer.',

    'payment_method.updated' => 'Mode de paiement mis a jour avec succes.',
    'payment_method.nothing_to_sync' => 'Aucun nouveau mode de paiement a appliquer.',
    'payment_method.address_required' => 'Une adresse de facturation est requise avant d ajouter un mode de paiement.',
    'payment_method.tax_location_invalid' => 'Votre adresse de facturation n a pas pu etre verifiee. Veuillez la corriger et reessayer.',
    'payment_method.provider_unavailable' => 'Le fournisseur de facturation n a pas pu etre joint. Veuillez reessayer.',

    'promotion_code.valid' => 'Code promotionnel applique.',

    'plan.limit_reached' => 'Vous avez atteint la limite de cette fonctionnalite pour votre forfait actuel.',

    'admin.plan.updated' => 'Forfait mis a jour avec succes.',
    'admin.plan.price_synced' => 'Prix du forfait synchronise avec succes.',

    'throttle' => 'Trop de requetes. Veuillez reessayer dans :seconds secondes.',

];
