# SneakUP – E-Commerce Platform

SneakUP is a full-stack web-based e-commerce platform developed as a final project for the Web Programming course. It allows users to browse, search, purchase, and review products, while administrators can manage all store content.

## Student Information

- **Name:** Muzychenko Artur  
- **Student ID:** 551568  
- **GitHub Username:** Muzychenko14  

---

## Features

### User Side
- User registration and login with session handling
- Product browsing and live search with image & price preview
- Category filter (Men, Women, Kids, Unisex)
- Add to Cart & Wishlist functionality with dynamic icons
- Place orders with 3 payment options: Cash, Credit Card, PayPal
- Order confirmation and history
- Product reviews with star ratings
- Responsive design with Tailwind CSS

### Admin Panel
- Admin-only access protected via session check
- Dashboard with total stats: orders, revenue, users, admins, max order
- Add/edit/delete products with image carousel
- View all orders with payment method, status & user info
- Drag & drop image upload for products

---

## Tech Stack

### Frontend:
- HTML5, JavaScript, Tailwind CSS
- Font Awesome icons
- Swiper.js for carousels

### Backend:
- PHP 8.x
- MySQL (via phpMyAdmin)
- Session-based authentication
- REST-like structure for admin handling

---

## Folder Structure

/admin → Admin dashboard & logic
/cart → Shopping cart, checkout, order handler
/profile → User profile page & JS
/wish → Wishlist management
/uploads → Product and avatar images
/css, js → Custom styles and scripts
index.php → Landing page
search.php → Search results
product.php → Product detail view
login.php → Authentication pages
register.php → Registration logic


---

## Development Decisions

- **Tailwind CSS** was used for rapid styling and clean UI.
- The project is **fully responsive** and designed to work across devices.
- **PHP sessions** manage user roles (admin/user) securely.
- **JavaScript** adds dynamic interactivity (live previews, modals, sliders).

---

## Screenshots

_(Included in pdf report)_

- Home Page  
- Search Suggestion  
- Cart & Wishlist  
- Admin Dashboard  
- Product Edit Modal

---

## Contact

For project-related inquiries:  
muzychenko1114@gmail.com  
GitHub: [Muzychenko14](https://github.com/Muzychenko14)

---

> Developed as part of the Web Programming course project @ **University of Messina**, 3rd Year – Data Analytics Program
