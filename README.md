# **SCU Library Management System**

## **📚 Project Overview**

The **SCU Library Management System** is a web-based application designed to manage books, users, borrowing history, and feedback for the **SCU Library**. This system allows students to register, borrow books, submit feedback, and manage their library account, all while providing the library staff with administrative control to manage books, users, and borrowings.

### **Core Features:**

* **User Registration and Login**: Secure authentication for students and staff.

* **Book Management**: Search, borrow, return, and review books.

* **User Dashboard**: View borrowed books, history, and feedback.

* **Admin Panel**: Manage users, books, and book inventory.

---

## **🛠 Technologies Used**

* **Frontend**: HTML, CSS, JavaScript, Elementor (WordPress)

* **Backend**: PHP (Custom Logic for Forms and Authentication)

* **Database**: MySQL (for storing user, book, borrowing data)

* **Platform**: WordPress (with Astra Child Theme for UI)

* **Local Server**: XAMPP (Apache \+ MySQL)

---

## **⚙️ Setup and Installation**

### **Prerequisites:**

* Install [**XAMPP**](https://www.apachefriends.org/index.html) to set up Apache and MySQL locally.

* Have [**WordPress**](https://wordpress.org/) installed on your local server.

### **Installation Steps:**

**Clone the repository**:

 git clone https://github.com/CodeArtistAladin/DBMS-final-project.git

1.   
2. **Set up XAMPP**:

   * Start **Apache** and **MySQL** services in the **XAMPP Control Panel**.

3. **Install WordPress**:

   * Extract WordPress in the `htdocs` folder of XAMPP (e.g., `C:\xampp\htdocs\wordpress`).

   * Create a new database named `sculibrary_db` using **phpMyAdmin**.

4. **Import the Database**:

   * Import the SQL file located in the `database` folder of this repository into the `sculibrary_db`.

5. **Configure WordPress**:

   * Navigate to the WordPress installation folder (`C:\xampp\htdocs\wordpress`) and configure `wp-config.php` to connect to the database.

   * Go to `http://localhost/wordpress` and follow the setup steps.

6. **Theme Installation**:

   * Install the **Astra Child Theme** and activate it in the WordPress dashboard.

   * Install required plugins if mentioned (e.g., **Elementor**).

---

## **📂 File Structure**

* **/scu-library-management**

  * **/assets**: Contains images, CSS files, and static assets.

  * **/includes**: PHP files for custom logic (authentication, user dashboard).

  * **/themes**: The custom WordPress theme with Astra Child for UI.

  * **/database**: SQL files for setting up MySQL database and tables.

  * **README.md**: Project overview, setup instructions, and documentation.

---

## **🔍 Database Design**

This system uses an **8-table relational database** with the following tables:

1. **users**: Stores user credentials and roles (e.g., student, librarian).

2. **authors**: Stores author details.

3. **books**: Stores book information (title, category, author).

4. **borrowers**: Stores user borrow records.

5. **feedback**: Stores user feedback on the library system.

6. **reviews**: Stores user reviews on books.

7. **inventory**: Tracks the availability and location of books in the library.  
   8\. **categories:** stores the categories of the books.

---

## **🌟 Features & Functionalities**

* **User Login/Registration**:

  * Secure registration system with role management (students and librarians).

* **Book Management**:

  * Admin can add, edit, delete books in the inventory.

* **Book Borrowing**:

  * Users can borrow books and return them after a specified period.

* **User Dashboard**:

  * Allows users to view their borrowing history, leave reviews, and provide feedback.

* **Admin Panel**:

  * Librarians can manage users and books, as well as view borrowing statistics.

---

## **💻 Demo**

To view a live demo of the system, you can:

1. Clone the repository and set it up locally (follow the installation instructions above).

2. Visit the login page (`http://localhost/wordpress/login.php`).

3. Register as a student or librarian and start using the library management system.

---

## **🔧 To Do / Future Features**

* AI-powered **Book Recommendations** based on user borrowing history.

* **User-specific Book Suggestions** using machine learning.

* **Mobile-friendly version** for easy use on smartphones and tablets.

* **Advanced Reporting**: Generate dynamic reports on user activity, borrowing patterns, and feedback.

---

## **📄 License**

This project is licensed under the MIT License – see the [LICENSE](https://docs.google.com/document/d/1-HXAJEei8t1IHwUO-BxwKODcEP2S5E_4PUHLMX2Nndc/edit?usp=sharing) file for details.

---

## **🤝 Contributing**

Feel free to fork this repository, submit issues, or make pull requests to improve the Library Management System. Contributions are welcome\!

---

This **README.md** gives a clear understanding of the **purpose, setup, and usage** of the project. It's informative, easy to follow, and structured well for any developer or user who accesses the repository.

