<?php

namespace App\Controllers;

class Authentication extends BaseController {
    
    public function index() {
        // If already logged in, send them to the Dashboard, NOT the login page
        if (session()->get('logged_in')) {
            // Updated: Redirect to dashboard instead of inventory
            return redirect()->to('dashboard'); 
        }
        return view('login');
    }

    public function loginProcess() {
        $session = session();
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $db = \Config\Database::connect();
        $user = $db->table('users')->where('username', $username)->get()->getRowArray();

        if ($user) {
            // Note: Keeping your existing plain-text password comparison logic as requested
            if ($password == $user['password']) {
                $session->set([
                    'user_id'   => $user['id'],
                    'username'  => $user['username'],
                    'role'      => $user['role'],
                    'logged_in' => TRUE
                ]);
                
                // Updated: Send to dashboard route after login
                return redirect()->to('dashboard'); 
            } else {
                return redirect()->back()->with('msg', 'Invalid Password');
            }
        } else {
            return redirect()->back()->with('msg', 'Username not found');
        }
    }

    public function logout() {
        session()->destroy();
        return redirect()->to('authentication');
    }
}