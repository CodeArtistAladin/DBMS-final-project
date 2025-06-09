<?php
<!DOCTYPE html>
<html data-theme="light">
<head>
    <style>
        :root[data-theme="light"] {
            --primary-red: #ff4444;
            --secondary-red: #ff8585;
            --dark-red: #cc0000;
            --light-red: #ffe6e6;
            --hover-red: #fa5252;
            --text-dark: #2d3436;
            --text-light: #636e72;
            --white: #ffffff;
            --bg-color: var(--light-red);
            --card-bg: var(--white);
        }

        :root[data-theme="dark"] {
            --primary-red: #ff6b6b;
            --secondary-red: #ff8585;
            --dark-red: #ff4444;
            --light-red: #4a1010;
            --hover-red: #ff5252;
            --text-dark: #ffffff;
            --text-light: #cccccc;
            --white: #1a1a1a;
            --bg-color: #121212;
            --card-bg: #2d2d2d;
        }

        .theme-switch {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--primary-red);
            color: var(--white);
            padding: 10px 20px;
            border-radius: 20px;
            cursor: pointer;
            border: none;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }

        .theme-switch:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-dark);
            transition: all 0.3s ease;
        }

        .profile-section, .book-card, .table-container {
            background: var(--card-bg);
        }
    </style>

    <script>
        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        }

        // Check for saved theme preference
        document.addEventListener('DOMContentLoaded', () => {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        });
    </script>
</head>