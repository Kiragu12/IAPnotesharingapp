<?php
/**
 * StubData - provides fake user and notes for UI testing without a database
 */
class StubData {
    public function getSampleUser() {
        return [
            'id' => 1,
            'username' => 'demouser',
            'full_name' => 'Demo User',
            'email' => 'demo@noteshareacademy.com'
        ];
    }

    public function getUserNotes($user_id = 1) {
        $now = date('Y-m-d H:i:s');
        return [
            [
                'id' => 101,
                'user_id' => $user_id,
                'title' => 'Calculus Cheatsheet',
                'content' => 'A concise cheatsheet covering derivatives and integrals.',
                'preview' => 'A concise cheatsheet covering derivatives and integrals.',
                'note_type' => 'text',
                'is_public' => 1,
                'file_path' => null,
                'file_name' => null,
                'tags' => 'calculus,math',
                'status' => 'published',
                'created_at' => $now,
                'updated_at' => $now,
                'view_count' => 42
            ],
            [
                'id' => 102,
                'user_id' => $user_id,
                'title' => 'Linear Algebra - Matrix Notes',
                'content' => 'Notes on matrix operations and eigenvalues.',
                'preview' => 'Notes on matrix operations and eigenvalues.',
                'note_type' => 'text',
                'is_public' => 0,
                'file_path' => null,
                'file_name' => null,
                'tags' => 'algebra,matrix',
                'status' => 'published',
                'created_at' => $now,
                'updated_at' => $now,
                'view_count' => 12
            ],
            [
                'id' => 103,
                'user_id' => $user_id,
                'title' => 'Sample PDF: Project Report',
                'content' => 'File upload: project_report.pdf',
                'preview' => 'File upload: project_report.pdf',
                'note_type' => 'file',
                'is_public' => 1,
                'file_path' => 'uploads/documents/sample_project_report.pdf',
                'file_name' => 'project_report.pdf',
                'file_size' => 24576,
                'file_type' => 'application/pdf',
                'tags' => 'project,report',
                'status' => 'published',
                'created_at' => $now,
                'updated_at' => $now,
                'view_count' => 7
            ]
        ];
    }

    public function getPublicNotes() {
        // Return a combined list of public notes
        $userNotes = $this->getUserNotes(1);
        $public = array_filter($userNotes, function($n) { return isset($n['is_public']) && $n['is_public']; });
        // Attach fake author info
        foreach ($public as &$note) {
            $note['full_name'] = 'Demo User';
            $note['email'] = 'demo@noteshareacademy.com';
        }
        return array_values($public);
    }

    public function getStats($user_id = 1) {
        $notes = $this->getUserNotes($user_id);
        $total = count($notes);
        $public = count(array_filter($notes, function($n){ return isset($n['is_public']) && $n['is_public']; }));
        return [
            'total_notes' => $total,
            'public_notes' => $public
        ];
    }

    public function getNavCounters() {
        $notes = $this->getUserNotes(1);
        return [
            'my_notes_count' => count($notes),
            'shared_notes_count' => count(array_filter($notes, function($n){ return isset($n['is_public']) && $n['is_public']; }))
        ];
    }
}

?>