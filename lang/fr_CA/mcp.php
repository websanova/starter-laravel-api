<?php

return [

    'app.instructions' => 'Outils permettant a l\'utilisateur authentifie de gerer les donnees de son propre compte. Les signets font reference aux etiquettes par id, jamais par nom. Trouvez les ids a partir des noms d\'etiquettes avec l\'outil de liste des etiquettes, et creez l\'etiquette si elle n\'existe pas.',

    'app.tools.bookmark.create.description' => 'Cree un signet pour l\'utilisateur authentifie.',
    'app.tools.bookmark.create.params.url' => 'L\'url du signet. Doit etre unique parmi les signets de l\'utilisateur.',
    'app.tools.bookmark.create.params.title' => 'Le titre du signet.',
    'app.tools.bookmark.create.params.is_favorited' => 'Indique si le signet est un favori.',
    'app.tools.bookmark.create.params.tag_ids' => 'Les ids des etiquettes existantes du signet. Trouvez-les avec l\'outil de liste des etiquettes, ne devinez jamais un id.',

    'app.tools.bookmark.delete.description' => 'Supprime un signet appartenant a l\'utilisateur authentifie.',
    'app.tools.bookmark.delete.params.bookmark' => 'L\'id du signet a supprimer.',

    'app.tools.bookmark.list.description' => 'Liste les signets de l\'utilisateur authentifie avec filtrage et tri optionnels.',
    'app.tools.bookmark.list.params.search' => 'Ne retourne que les signets correspondant a ce terme de recherche.',
    'app.tools.bookmark.list.params.favorited' => 'Ne retourne que les signets qui sont des favoris ou qui n\'en sont pas.',
    'app.tools.bookmark.list.params.tag_id' => 'Ne retourne que les signets ayant cet id d\'etiquette. Trouvez-le avec l\'outil de liste des etiquettes, ne devinez jamais un id.',
    'app.tools.bookmark.list.params.sort_by' => 'Le champ de tri.',
    'app.tools.bookmark.list.params.sort_dir' => 'Le sens du tri.',
    'app.tools.bookmark.list.params.per_page' => 'Le nombre de signets par page.',

    'app.tools.bookmark.update.description' => 'Met a jour un signet appartenant a l\'utilisateur authentifie. Seuls les champs fournis sont modifies.',
    'app.tools.bookmark.update.params.bookmark' => 'L\'id du signet a mettre a jour.',
    'app.tools.bookmark.update.params.url' => 'Le nouvel url du signet. Doit etre unique parmi les signets de l\'utilisateur.',
    'app.tools.bookmark.update.params.title' => 'Le nouveau titre du signet.',
    'app.tools.bookmark.update.params.is_favorited' => 'Indique si le signet est un favori.',
    'app.tools.bookmark.update.params.tag_ids' => 'La liste complete des ids d\'etiquettes existantes du signet. Trouvez-les avec l\'outil de liste des etiquettes, ne devinez jamais un id. Remplace les etiquettes existantes, et une liste vide les retire toutes.',

    'app.tools.profile.show.description' => 'Affiche le profil de l\'utilisateur authentifie, y compris son forfait et ses abonnements.',

    'app.tools.profile.update.description' => 'Met a jour le profil de l\'utilisateur authentifie. Seuls les champs fournis sont modifies.',
    'app.tools.profile.update.params.first_name' => 'Le nouveau prenom de l\'utilisateur.',
    'app.tools.profile.update.params.last_name' => 'Le nouveau nom de famille de l\'utilisateur.',
    'app.tools.profile.update.params.locale' => 'La nouvelle langue de l\'utilisateur, par exemple "en".',
    'app.tools.profile.update.params.timezone' => 'Le nouveau fuseau horaire de l\'utilisateur sous forme d\'identifiant IANA, par exemple "America/Toronto".',

    'app.tools.tag.create.description' => 'Cree une etiquette pour l\'utilisateur authentifie. Listez d\'abord les etiquettes existantes, car les noms d\'etiquettes doivent etre uniques. Retourne l\'id de la nouvelle etiquette a utiliser dans les outils de signets.',
    'app.tools.tag.create.params.name' => 'Le nom de l\'etiquette. Jusqu\'a 50 caracteres, doit commencer par une lettre ou un chiffre, et doit etre unique parmi les etiquettes de l\'utilisateur.',

    'app.tools.tag.delete.description' => 'Supprime une etiquette appartenant a l\'utilisateur authentifie. L\'etiquette est retiree de chaque signet qui l\'utilise.',
    'app.tools.tag.delete.params.tag' => 'L\'id de l\'etiquette a supprimer.',

    'app.tools.tag.list.description' => 'Liste les etiquettes de l\'utilisateur authentifie avec leurs ids et leurs noms. Utilisez cet outil pour trouver les ids necessaires aux champs tag_ids et tag_id des outils de signets.',

    'app.tools.tag.update.description' => 'Renomme une etiquette appartenant a l\'utilisateur authentifie.',
    'app.tools.tag.update.params.tag' => 'L\'id de l\'etiquette a mettre a jour.',
    'app.tools.tag.update.params.name' => 'Le nouveau nom de l\'etiquette. Jusqu\'a 50 caracteres, doit commencer par une lettre ou un chiffre, et doit etre unique parmi les etiquettes de l\'utilisateur.',

    'admin.instructions' => 'Outils permettant aux administrateurs de gerer les utilisateurs et les donnees de l\'application.',

    'admin.tools.hello.description' => 'Salue l\'administrateur authentifie. Utilisez cet outil pour confirmer que la connexion fonctionne et avec quel compte administrateur elle est authentifiee.',

];
