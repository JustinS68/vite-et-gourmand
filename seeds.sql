-- Insert sample users
INSERT INTO users (email, password, nom, prenom, role) VALUES
('Jose33@viteetgourmand.fr', '$2y$10$hash1', 'Dupont', 'Jose', 'administrateur'),
('Julie33@viteetgourmand.fr', '$2y$10$hash2', 'Dupont', 'Julie', 'employee'),
('aquitaine@gmail.com', '$2y$10$hash3', 'Bernard', 'Michel', 'user');

-- Insert sample menus
INSERT INTO menus (nom, description, prix, categorie, nombre_personne) VALUES
('Buffet Reveillon Noel', 'Menu traditionnel de reveillon avec foie gras et champagne', 85.00, 'Fete', 20),
('Buffet Paques', 'Menu pascal avec agneau et specialites de saison', 65.00, 'Fete', 15),
('Buffet Vegetarien', 'Selection complete de plats vegetariens et vegans', 45.00, 'Vegetarien', 20),
('Buffet Entreprise', 'Menu ideal pour les repas d\'affaires et seminaires', 55.00, 'Entreprise', 30),
('Buffet BBQ', 'Grillades et accompagnements frais pour vos jardins', 50.00, 'Loisir', 25),
('Menu Gastronomique', 'Decouvrez notre selection haut de gamme', 95.00, 'Premium', 10),
('Buffet Froid d\'Ete', 'Salades fraiches et plats legers pour l\'ete', 40.00, 'Ete', 20),
('Menu Traditionnel Bordelais', 'Specialites culinaires de Bordeaux et region', 70.00, 'Regional', 15);

-- Insert sample reviews
INSERT INTO avis (user_id, commentaire, note, valide) VALUES
(3, 'Excellent service et nourriture delicieuse! Tres professionnel.', 5, 1),
(3, 'Les buffets etaient frais et savoureux. Je recommande vivement!', 5, 1),
(2, 'Super experience, equipe sympathique et reactive.', 5, 1),
(3, 'Tres bon rapport qualite-prix, menus varies et appetissants.', 4, 1);

-- Insert sample contact messages
INSERT INTO contact (nom, email, sujet, message) VALUES
('Dupont Marie', 'marie.dupont@email.com', 'Demande de devis', 'Bonjour, je souhaite un devis pour un buffet de 50 personnes pour un anniversaire.'),
('Bernard Jacques', 'jacques.bernard@email.com', 'Allergies alimentaires', 'Pouvez-vous preparer des menus sans gluten et sans lactose?'),
('Moreau Sophie', 'sophie.moreau@email.com', 'Proposition de theme', 'Avez-vous des menus exotiques ou asiatiques disponibles?');

