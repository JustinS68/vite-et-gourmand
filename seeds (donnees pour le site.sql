
USE vite_gourmand;

INSERT INTO ALLERGENE (nom) VALUES ('fruits de mer'), ('produits laitiers'), ('ble'), ('poissons'), ('alcool');

INSERT INTO UTILISATEUR (nom, prenom, email, mot_de_passe, telephone, adresse, role, actif)
VALUES
('Dupont', 'Jose', 'Dupont_Jose@viteetgourmand.fr','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.ucrIJ5g2', '0612121414', 'Bordeaux', 'administrateur', TRUE), -- id1
('Dupont', 'Julie', 'Dupont_Julie@viteetgourmand.fr','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.ucrIJ5g2','0625242368', 'Bordeaux', 'employe', TRUE), -- id2
('Henry', 'Bernard', 'Bernard_aquitaine@gmail.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.ucrIJ5g2','0721547895', 'Pessac', 'utilisateur', TRUE), -- id3
('Schmitt', 'Marcel', 'Schmarcel@hotmail.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.ucrIJ5g2', '0712345698', 'Talence', 'utilisateur', TRUE); -- id4

INSERT INTO MENU (titre, description, theme, regime, nb_personnes_min, prix_base, stock)
VALUES
('Christmas Eve', 'Banquet traditionnel de Noel', 'noel', 'classique', 15, 45, 12), -- id1
('Week-end de Paques', 'Repas pour le dimanche de Paques', 'paques', 'classique', 5, 32, 8), -- id2
('Vegetarien a plusieurs', '100% vegetarien, 100% delicieux', 'vegetarien', 'vegetarien', 3, 18, 7), -- id3
('Romantique', 'C est beau l amour', 'romantique', 'classique', 2, 75, 21), -- id4
('Banquet d entreprise', 'Bien mange en quantite', 'evenement', 'classique', 40, 12, 4); -- id5

INSERT INTO PLAT (nom, type, description)
VALUES
('huitres', 'entree', 'huitres fraiche du bassin d arcachon'), -- id1
('toast foie gras', 'entree', 'toast de foie gras et sa confiture de figue'), -- id2
('toast saumon', 'entree', 'toast beurre au saumon frais de norvege'), -- id3
('pain surprise', 'entree', 'pain surprise divers et varies au jambon, concombre et fromage frais'), -- id4
('toast courgette', 'entree', 'toast de courgette confites'), -- id5
('toast tomates sechees', 'entree', 'toast de tomates sechees sur un lit de caviar d aubergine'), -- id6
('crevettes et sauce tartates', 'entree', 'crevette de l atlantique nord decortique servie avec une sauce tartare'), -- id7
('chapon farci', 'plat', 'chapon farci a la chataigne servi avec choux rouge et salade verte'), -- id8
('salade chevre chaud', 'plat', 'salade de chevre chaud compose de salade verte, tomates cerises, pignon de pin, oignons confis et chevre chaud'), -- id9
('jambon cuit', 'plat', 'jambon cuit a feux doux pendant plusieurs heures servi avec sa salade verte'), -- id10
('lapin sauce vin rouge', 'plat', 'lapin servi avec un reduction sauce vin rouge et sa salade verte'), -- id11
('aubergine a la bonifacienne', 'plat', 'aubergine servie comme a bonifaccio sur un lit de sauce tomate maison'), -- id12
('gnocchi sauce champignon', 'plat', 'gnocchi de pomme de terre servi avec une sauce maison de champignon de la region'), -- id13
('sanglier aux olives', 'plat', 'sanglier corse servi avec une sauce aux olives et pates fraiches'), -- id14
('poulet braise', 'plat', 'poulet braise a la broche servi avec sa salade verte'), -- id15
('pates bolognaise', 'plat', 'pates servi avec sa sauce bolognaise compose de tomate du jardin et viande 100% de boeuf'), -- id16
('buche de noel', 'dessert', 'buche traditionnelle de noel saveur framboise ou citron ou chocolat'), -- id17
('fondant chocolat', 'dessert', 'creation originale de Julie avec son fondant au chocolat et des eclats de noisettes'), -- id18
('vacherin glacee', 'dessert', 'vacherin glacee gout fraise/citron'), -- id19
('salade de fruit', 'dessert', 'salade de fruits frais du marche du jour composee d oranges, cerises, fraises, framboises et pommes granny smith'), -- id20
('salade de fraises', 'dessert', 'salade de fraise servi sur un coulis de fraise et sa menthe fraiche du jardin'), -- id21
('Irish coffee', 'dessert', 'Irish coffee comme a Dublin avec Tullamore Dew 5ans d age'), -- id22
('mousse au chocolat', 'dessert', 'mousse au chocolat maison servie en verrine'), -- id23
('salade pomme/fraise/banane', 'dessert', 'salade rafraichissante et coloree avec pomme, fraise et banane'); -- id24

INSERT INTO MENU_PLAT (id_menu, id_plat) 
VALUES
(1,1), -- Christmas eve contient huitres
(1,2), -- Christmas eve contient toast foie gras
(1,3), -- Christmas eve contient toast saumon
(1,8), -- Christmas eve contient chapon farci
(1,17), -- Christmas eve contient buche de noel
(2,3), -- Paques contient aussi toast saumon
(2,4), -- Paques contient pain surprise
(2,9), -- Paques contient salade chevre chaud
(2,10), -- Paques contient jambon cuit
(2,11), -- Paques contient lapin sauve vin rouge
(2,18), -- Paques contient fondant chocolat
(2,19), -- Paques contient vacherin glacee
(3,5), -- Vegetarien contient toast courgette
(3,6), -- Vegetarien contient toast tomates sechees
(3,12), -- Vegetarien contient aubergine a la bonifacienne
(3,13), -- Vegetarien contient gnocchi sauce champigon
(3,20), -- Vegetarien contient salade de fruits
(4,1), -- Romantique contient aussi huitres
(4,7), -- Romantique contient crevettes
(4,14), -- Romantique contient sangliers aux olives
(4,15), -- Romantique contient poulet braise
(4,9), -- Romantique contient aussi salade chevre chaud
(4,21), -- Romantique contient salade de fraise
(4,18), -- Romantique contient aussi fondant chocolat
(4,22), -- Romantique contient Irish coffee
(5,4), -- Evenement contient aussi pain surprise
(5,6), -- Evenement contient aussi toast tomates sechees
(5,13), -- Evenement contient aussi gnocchi sauce champigon
(5,16), -- Evenement contient pates bolognaise
(5,23), -- Evenement contient mousse chocolat
(5,24); -- Evenement contient salade pomme/fraise/banane

INSERT INTO PLAT_ALLERGENE (id_plat, id_allergene)
VALUES
-- fruits de mer=id 1
-- produits laitiers=id 2
-- ble=id 3
-- poissons=id 4
-- alcool=id 5
(1,1), -- huitres contient fruits de mer
(3,1), -- toast saumon contient fruits de mer
(7,1), -- crevettes et sauce tartare contient fruits de mer
(3,2), -- toast saumon contient aussi produits laitiers
(4,2), -- pain surprise contient produits laitiers
(7,2), -- crevettes et sauce tartare contient aussi produits laitiers
(9,2), -- salade chevre chaud contient produits laitiers
(12,2), -- aubergine a la bonifacienne contient produits laitiers
(13,2), -- gnocchi sauce champignon contient produits laitiers
(17,2), -- buche de noel contient produits laitiers
(18,2), -- fondant chocolat contient produits laitiers
(19,2), -- vacherin glace contient produits laitiers
(23,2), -- mousse au chocolat contient produits laitiers
(2,3), -- toast foie gras contient ble
(3,3), -- toast saumon contient aussi ble
(4,3), -- pain surprise contient aussi ble
(5,3), -- toast courgette contient ble
(6,3), -- toast tomates sechee contient ble
(14,3), -- sanglier aux olives contient ble
(16,3), -- pates bolognaise contient ble
(22,3), -- Irish coffee contient ble
(1,4), -- huitres contient aussi poissons
(3,4), -- toast saumon contient aussi poissons
(7,4), -- crevettes et sauce tartare contient aussi poissons
(11,5), -- lapin sauce vin rouge contient alcool
(22,5); -- Irish coffee contient alcool

INSERT INTO IMAGE_MENU (id_menu, url)
VALUES
(1,'https://images.unsplash.com/photo-1729269524124-1645db0b0ff5?q=80&w=412&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'),
(1,'https://images.unsplash.com/photo-1627308594171-ebd99b564ff6?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8M3x8c2FsbW9uJTIwdG9hc3R8ZW58MHx8MHx8fDA%3D'),
(1,'https://plus.unsplash.com/premium_photo-1738802845911-809a01acfa50?q=80&w=387&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'),
(1,'https://plus.unsplash.com/premium_photo-1697377323208-9dd5ce916a66?q=80&w=387&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'),
(2,'https://images.unsplash.com/photo-1608032076946-23d89c6d6d0b?q=80&w=387&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'),
(2,'https://source.unsplash.com/fr/photos/salade-de-legumes-dans-un-bol-en-acier-inoxydable-55hOS2dRBPI?utm_source=unsplash&utm_medium=referral&utm_content=creditShareLink'),
(2,'https://source.unsplash.com/fr/photos/salade-de-legumes-dans-un-bol-en-acier-inoxydable-55hOS2dRBPI?utm_source=unsplash&utm_medium=referral&utm_content=creditShareLink'),
(2,'https://source.unsplash.com/fr/photos/une-personne-tranchant-du-jambon-sur-une-planche-a-decouper-MTWq0xeGTHs?utm_source=unsplash&utm_medium=referral&utm_content=creditShareLink'),
(3,'https://source.unsplash.com/fr/photos/sandwich-a-lassiette-JLpcpD_rNBI?utm_source=unsplash&utm_medium=referral&utm_content=creditShareLink'),
(3,'https://source.unsplash.com/fr/photos/gnocchis-a-la-sauce-cremeuse-et-eminces-de-truffes-OsYdnv2vVt0?utm_source=unsplash&utm_medium=referral&utm_content=creditShareLink'),
(3,'https://source.unsplash.com/fr/photos/une-assiette-de-nourriture-2cXZQ862gws?utm_source=unsplash&utm_medium=referral&utm_content=creditShareLink'),
(4,'https://source.unsplash.com/fr/photos/aliments-cuits-sur-un-bol-en-ceramique-noire-HNmcgpzPHag?utm_source=unsplash&utm_medium=referral&utm_content=creditShareLink'),
(4,'https://source.unsplash.com/fr/photos/poulet-frit-sur-plateau-noir--n0s7y7Nr2A?utm_source=unsplash&utm_medium=referral&utm_content=creditShareLink'),
(4,'https://source.unsplash.com/fr/photos/un-verre-de-biere-sur-un-sous-verre-v5zwfzsTfDk?utm_source=unsplash&utm_medium=referral&utm_content=creditShareLink'),
(5,'https://source.unsplash.com/fr/photos/un-verre-de-biere-sur-un-sous-verre-v5zwfzsTfDk?utm_source=unsplash&utm_medium=referral&utm_content=creditShareLink'),
(5,'https://source.unsplash.com/fr/photos/trois-verres-transparents-a-pied-bas-avec-des-aliments-du-desert-au-chocolat-Z4zLE_8VZTc?utm_source=unsplash&utm_medium=referral&utm_content=creditShareLink');

INSERT INTO COMMANDE (id_utilisateur, id_menu, nb_personnes, date_prestation, heure_prestation, adresse_prestation, ville_prestation, statut, prix_total, prix_livraison)
VALUES
(3, 1, 15, '2026-12-24 00:00:00', '20:00:00', '28 av. Jean Jaures', 'Pessac', 'terminee', 675.00, 5.59), -- id1
(4, 3, 28, '2026-09-12 00:00:00', '11:45:00', '12 rue Robespierre', 'Talence', 'en attente retour materiel', 504.00, 5.59), -- id2
(3, 4, 2, '2026-06-21 00:00:00', '19:30:00', '28 av. Jean Jaures', 'Pessac', 'accepte', 150.00, 5.59); -- id3

INSERT INTO SUIVI_COMMANDE (id_commande, statut, id_employe)
VALUES
(1, 'accepte', 2),
(1, 'en preparation', 2),
(1, 'en cours de livraison', 2),
(1, 'livre', 2),
(1, 'terminee', 2),
(2, 'accepte', 2),
(2, 'en preparation', 2),
(2, 'en cours de livraison', 2),
(2, 'livre', 2),
(2, 'en attente retour materiel', 2),
(3, 'accepte', 2);

INSERT INTO AVIS (id_commande, id_utilisateur, note, commentaire, valide)
VALUES
(1, 3, 5, 'nous avons commande le repas du reveillon avec vite et gourmand et le service etait vraiment impeccable, la nourriture etait delicieuse et nous reviendrons pour sur! BRAVO', TRUE),
(2, 4, 4, 'mes amis et moi sommes vegetarien et avons toujours du mal a trouver ou manger, vite et gourmand est vraiment genial, un menu frais et complet ET vegetrien. SUPER, de plus le suivi est excellent et vous explique clairement comment rendre le materiel (si vous l avez loue evidemment)', TRUE),
(1, 3, 5, 'SUPER EXPERIENCE!', TRUE);








