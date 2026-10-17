<?php

return [

    'admin' => [
        'plan' => [
            'price_synced' => 'Prix du forfait synchronise avec succes.',
            'updated' => 'Forfait mis a jour avec succes.',
        ],
        'user' => [
            'avatar_destroyed' => 'Avatar de l utilisateur supprime avec succes.',
            'deleted' => 'Utilisateur supprime avec succes.',
            'force_deleted' => 'Utilisateur supprime definitivement.',
            'password_reset' => 'Un mot de passe temporaire a ete envoye a l utilisateur.',
            'restored' => 'Utilisateur restaure avec succes.',
            'role_updated' => 'Role de l utilisateur mis a jour avec succes.',
            'subscription_cancelled' => 'Abonnement de l utilisateur annule avec succes.',
            'subscription_coupon_applied' => 'Coupon applique a l abonnement de l utilisateur avec succes.',
            'subscription_coupon_removed' => 'Coupon retire de l abonnement de l utilisateur avec succes.',
            'subscription_created' => 'Abonnement de l utilisateur cree avec succes.',
            'subscription_resumed' => 'Abonnement de l utilisateur repris avec succes.',
            'updated' => 'Utilisateur mis a jour avec succes.',
        ],
    ],
    'auth' => [
        'deleted' => 'Ce compte a ete supprime.',
        'failed' => 'Les informations d identification fournies sont incorrectes.',
        'forbidden' => 'Vous n avez pas la permission d acceder a cette ressource.',
        'password_reset_required' => 'Vous devez mettre a jour votre mot de passe avant de continuer.',
        'throttle' => 'Trop de tentatives de connexion. Veuillez reessayer dans :seconds secondes.',
        'unverified' => 'Votre compte n est pas verifie.',
    ],
    'avatar' => [
        'destroyed' => 'Avatar supprime avec succes.',
        'stored' => 'Avatar televerse avec succes.',
    ],
    'bookmark' => [
        'created' => 'Signet cree avec succes.',
        'deleted' => 'Signet supprime avec succes.',
        'updated' => 'Signet mis a jour avec succes.',
    ],
    'email_change' => [
        'confirmed' => 'Votre adresse courriel a ete mise a jour.',
        'invalid_token' => 'Ce jeton de changement de courriel est invalide ou a expire.',
        'sent' => 'Un lien de confirmation a ete envoye a votre nouvelle adresse courriel.',
        'throttled' => 'Veuillez patienter avant de demander un autre changement de courriel.',
    ],
    'model_not_found' => 'Aucun resultat pour :model id :id.',
    'password' => [
        'updated' => 'Votre mot de passe a ete mis a jour.',
    ],
    'passwords' => [
        'reset' => 'Votre mot de passe a ete reinitialise.',
        'sent' => 'Nous vous avons envoye par courriel votre lien de reinitialisation de mot de passe.',
        'sent_if_exists' => 'Si ce courriel est enregistre, un lien de reinitialisation de mot de passe a ete envoye.',
        'throttled' => 'Veuillez patienter avant de reessayer.',
        'token' => 'Ce jeton de reinitialisation de mot de passe est invalide.',
        'user' => 'Aucun compte n a ete trouve avec cette adresse courriel.',
    ],
    'payment_method' => [
        'address_invalid' => 'Cette adresse n a pas pu etre verifiee. Veuillez la corriger et reessayer.',
        'address_required' => 'Une adresse de facturation est requise avant d ajouter un mode de paiement.',
        'address_updated' => 'Adresse de facturation mise a jour avec succes.',
        'customer_missing' => 'Aucun compte de facturation n a ete trouve pour votre compte. Veuillez contacter le soutien.',
        'nothing_to_sync' => 'Aucun nouveau mode de paiement a appliquer.',
        'provider_unavailable' => 'Le fournisseur de facturation n a pas pu etre joint. Veuillez reessayer.',
        'subscription_active' => 'Votre mode de paiement ne peut pas etre retire tant que votre abonnement se renouvelle.',
        'updated' => 'Mode de paiement mis a jour avec succes.',
    ],
    'plan' => [
        'limit_reached' => 'Vous avez atteint la limite de cette fonctionnalite pour votre forfait actuel.',
    ],
    'subscription' => [
        'cancelled' => 'Abonnement annule avec succes.',
        'created' => 'Abonnement cree avec succes.',
        'nothing_to_resume' => 'Il n y a aucun abonnement a reprendre.',
        'nothing_to_sync' => 'Aucun paiement complete a appliquer.',
        'payment_failed' => 'Votre mode de paiement a ete refuse. Mettez le a jour et reessayez.',
        'payment_method_missing' => 'Un mode de paiement est requis avant de reprendre votre abonnement.',
        'payment_method_required' => 'Un mode de paiement est requis avant de vous abonner.',
        'required' => 'Un abonnement actif est requis pour acceder a cette ressource.',
        'resumed' => 'Abonnement repris avec succes.',
        'updated' => 'Abonnement mis a jour avec succes.',
    ],
    'tag' => [
        'created' => 'Etiquette creee avec succes.',
        'deleted' => 'Etiquette supprimee avec succes.',
        'updated' => 'Etiquette mise a jour avec succes.',
    ],
    'throttle' => 'Trop de requetes. Veuillez reessayer dans :seconds secondes.',
    'verification' => [
        'channel_disabled' => 'La verification n\'est pas disponible pour ce canal.',
        'channel_unavailable' => 'Il n\'y a rien ou envoyer un code pour ce canal.',
        'invalid_code' => 'Le code de verification est incorrect.',
        'no_valid_code' => 'Aucun code de verification valide trouve. Veuillez en demander un nouveau.',
        'sent' => 'Code de verification envoye.',
        'throttled' => 'Veuillez patienter avant de demander un nouveau code.',
        'verified' => 'Verifie avec succes.',
    ],

];
