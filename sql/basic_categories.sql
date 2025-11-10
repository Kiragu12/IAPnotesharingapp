-- Basic Categories for Notes System
-- Run this after setting up the main database

INSERT INTO categories (name, description, created_at) VALUES
('Mathematics', 'Mathematical concepts, formulas, and problem solving', NOW()),
('Science', 'Biology, Chemistry, Physics, and other natural sciences', NOW()),
('Computer Science', 'Programming, algorithms, data structures, and software engineering', NOW()),
('History', 'Historical events, dates, and cultural studies', NOW()),
('Literature', 'Literary analysis, poetry, novels, and writing techniques', NOW()),
('Languages', 'Foreign language learning, grammar, and vocabulary', NOW()),
('Business', 'Economics, finance, marketing, and business strategy', NOW()),
('Art & Design', 'Visual arts, graphic design, and creative studies', NOW()),
('Engineering', 'Mechanical, electrical, civil, and other engineering disciplines', NOW()),
('General', 'Miscellaneous notes that don\'t fit other categories', NOW());
