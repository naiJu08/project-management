-- Test Wiki Data for Project 14
-- E-Commerce Platform Requirements Documentation
-- Run this SQL to populate wiki pages for testing

-- Sample Wiki Pages for Project 14
INSERT INTO `wiki_pages` (`project_id`, `title`, `content`, `parent_id`, `created_by`, `updated_by`, `order`, `version`, `created_at`, `updated_at`) VALUES
(14, 'Project Overview', '<h1>E-Commerce Platform Project</h1>
<p>This document outlines the requirements for building a modern e-commerce platform with the following key features:</p>
<ul>
  <li><strong>User authentication and authorization</strong> - Secure login and registration</li>
  <li><strong>Product catalog with categories</strong> - Hierarchical product organization</li>
  <li><strong>Shopping cart functionality</strong> - Real-time cart management</li>
  <li><strong>Payment gateway integration</strong> - Multiple payment methods</li>
  <li><strong>Order management system</strong> - Complete order lifecycle</li>
  <li><strong>Admin dashboard</strong> - Comprehensive admin controls</li>
</ul>
<h2>Target Users</h2>
<p>The platform will serve three types of users:</p>
<ol>
  <li><strong>Customers</strong>: Browse products, make purchases, track orders</li>
  <li><strong>Vendors</strong>: Manage their product listings, view sales reports</li>
  <li><strong>Administrators</strong>: Oversee entire platform, manage users and content</li>
</ol>
<h2>Technical Stack</h2>
<p>The platform will be built using:</p>
<ul>
  <li>Backend: Laravel 9+ with RESTful APIs</li>
  <li>Frontend: React/Vue.js for dynamic UI</li>
  <li>Database: MySQL for relational data</li>
  <li>Cache: Redis for session and cache management</li>
  <li>Payment: Stripe and PayPal integration</li>
</ul>', NULL, 1, 1, 0, 1, NOW(), NOW()),

(14, 'User Management Requirements', '<h1>User Management Module</h1>
<h2>User Registration</h2>
<p>As a new customer, I want to register on the platform by providing:</p>
<ul>
  <li><strong>Email address</strong> (must be unique and validated)</li>
  <li><strong>Password</strong> (minimum 8 characters with complexity requirements: uppercase, lowercase, number, special character)</li>
  <li><strong>Full name</strong> (First name and Last name)</li>
  <li><strong>Phone number</strong> (with country code validation)</li>
  <li><strong>Shipping address</strong> (Street, City, State, ZIP, Country)</li>
</ul>
<p><strong>Acceptance Criteria:</strong></p>
<ul>
  <li>Email verification link sent after registration</li>
  <li>Account activated only after email verification</li>
  <li>Duplicate email prevention</li>
  <li>Password strength indicator during registration</li>
</ul>
<h2>User Authentication</h2>
<p>Users should be able to log in using email and password. The system should support:</p>
<ul>
  <li><strong>Remember me functionality</strong> - Keep users logged in for 30 days</li>
  <li><strong>Password reset via email</strong> - Secure token-based reset</li>
  <li><strong>Two-factor authentication (optional)</strong> - SMS or authenticator app</li>
  <li><strong>OAuth integration</strong> - Google, Facebook login</li>
  <li><strong>Session management</strong> - Automatic logout after 2 hours of inactivity</li>
</ul>
<h2>User Profiles</h2>
<p>Users should be able to update their profile information including:</p>
<ul>
  <li>Personal details (name, email, phone)</li>
  <li>Profile picture upload</li>
  <li>Multiple shipping addresses with labels (Home, Office, etc.)</li>
  <li>Payment methods (saved cards, PayPal accounts)</li>
  <li>Communication preferences (email, SMS notifications)</li>
  <li>Privacy settings</li>
</ul>
<p><strong>Estimated Effort:</strong> 40 hours</p>', 1, 1, 1, 1, 1, NOW(), NOW()),

(14, 'Product Catalog', '<h1>Product Catalog Requirements</h1>
<h2>Product Listing</h2>
<p>The platform needs a comprehensive product catalog that supports:</p>
<ul>
  <li><strong>Product categories and subcategories</strong> - Up to 3 levels deep</li>
  <li><strong>Product search</strong> - Full-text search with autocomplete</li>
  <li><strong>Advanced filters</strong>:
    <ul>
      <li>Price range slider</li>
      <li>Brand selection</li>
      <li>Size, Color, Material attributes</li>
      <li>Availability (In Stock, Out of Stock)</li>
      <li>Customer ratings (4+ stars, etc.)</li>
    </ul>
  </li>
  <li><strong>Product sorting options</strong>:
    <ul>
      <li>Price: Low to High / High to Low</li>
      <li>Popularity (most viewed/purchased)</li>
      <li>Newest arrivals</li>
      <li>Customer ratings</li>
    </ul>
  </li>
  <li><strong>Product images</strong> - Support for up to 10 images per product</li>
  <li><strong>Product descriptions</strong> - Rich text editor support</li>
  <li><strong>Product specifications</strong> - Key-value pairs for technical details</li>
  <li><strong>Stock management</strong> - Real-time inventory tracking</li>
  <li><strong>Price management</strong> - Regular price, sale price, bulk discounts</li>
</ul>
<h2>Product Details Page</h2>
<p>Each product page should display:</p>
<ul>
  <li><strong>Image gallery</strong> - Main image with thumbnails, zoom on hover</li>
  <li><strong>Product title and SKU</strong></li>
  <li><strong>Price display</strong> - Original price, sale price, discount percentage</li>
  <li><strong>Stock status</strong> - "In Stock" with quantity, "Low Stock" warning, "Out of Stock"</li>
  <li><strong>Product description</strong> - Formatted text with images</li>
  <li><strong>Specifications table</strong> - Technical details in tabular format</li>
  <li><strong>Customer reviews section</strong>:
    <ul>
      <li>Overall rating (stars and number)</li>
      <li>Rating distribution chart</li>
      <li>Individual reviews with user name, date, rating, comment</li>
      <li>Helpful votes on reviews</li>
      <li>Pagination for reviews</li>
    </ul>
  </li>
  <li><strong>Related products</strong> - 4-6 similar products</li>
  <li><strong>Action buttons</strong>:
    <ul>
      <li>Add to Cart (with quantity selector)</li>
      <li>Add to Wishlist</li>
      <li>Share on social media</li>
    </ul>
  </li>
</ul>
<p><strong>Estimated Effort:</strong> 60 hours</p>', 1, 1, 1, 2, 1, NOW(), NOW()),

(14, 'Shopping Cart & Checkout', '<h1>Shopping Cart & Checkout Process</h1>
<h2>Shopping Cart Functionality</h2>
<p>As a customer, I want to add products to my cart and:</p>
<ul>
  <li><strong>View all items in cart</strong> - Product image, name, price, quantity, subtotal</li>
  <li><strong>Update quantities</strong> - Increase/decrease with +/- buttons</li>
  <li><strong>Remove items</strong> - Delete button for each item</li>
  <li><strong>Real-time calculations</strong>:
    <ul>
      <li>Subtotal (sum of all items)</li>
      <li>Discount amount (if coupon applied)</li>
      <li>Estimated tax</li>
      <li>Shipping cost</li>
      <li>Grand total</li>
    </ul>
  </li>
  <li><strong>Apply discount coupons</strong> - Coupon code input with validation</li>
  <li><strong>Estimated shipping costs</strong> - Based on delivery location</li>
  <li><strong>Save cart for later</strong> - Cart persists across sessions</li>
  <li><strong>Move to wishlist</strong> - Save items for future purchase</li>
</ul>
<h2>Checkout Process</h2>
<p>The checkout should be a simple, secure 3-step process:</p>
<h3>Step 1: Shipping Information</h3>
<ul>
  <li>Select from saved addresses or add new address</li>
  <li>Address validation with autocomplete</li>
  <li>Choose shipping method:
    <ul>
      <li>Standard Shipping (5-7 business days) - Free for orders over $50</li>
      <li>Express Shipping (2-3 business days) - $9.99</li>
      <li>Next Day Delivery - $19.99</li>
    </ul>
  </li>
  <li>Gift wrapping option ($5 extra)</li>
  <li>Delivery instructions (optional text field)</li>
</ul>
<h3>Step 2: Payment Method</h3>
<ul>
  <li><strong>Credit/Debit Card</strong>:
    <ul>
      <li>Card number, expiry, CVV input</li>
      <li>Support for Visa, Mastercard, Amex, Discover</li>
      <li>Option to save card for future use</li>
    </ul>
  </li>
  <li><strong>PayPal</strong> - Redirect to PayPal for payment</li>
  <li><strong>Cash on Delivery</strong> - Pay when order is delivered</li>
  <li><strong>Wallet/Store Credit</strong> - Use account balance</li>
</ul>
<h3>Step 3: Review & Confirm</h3>
<ul>
  <li>Complete order summary:
    <ul>
      <li>Items in order</li>
      <li>Shipping address</li>
      <li>Billing address</li>
      <li>Payment method</li>
      <li>Price breakdown</li>
    </ul>
  </li>
  <li>Edit option for each section</li>
  <li>Terms and conditions checkbox (required)</li>
  <li>Place Order button</li>
  <li>Order confirmation page with order number</li>
</ul>
<p><strong>Security Requirements:</strong></p>
<ul>
  <li>SSL encryption for entire checkout process</li>
  <li>PCI DSS compliance for payment processing</li>
  <li>Fraud detection and prevention</li>
  <li>3D Secure authentication for cards</li>
</ul>
<p><strong>Estimated Effort:</strong> 50 hours</p>', 1, 1, 1, 3, 1, NOW(), NOW()),

(14, 'Order Management', '<h1>Order Management System</h1>
<h2>Customer Order Management</h2>
<p>Customers should be able to:</p>
<ul>
  <li><strong>View order history</strong> - Complete list of all past orders</li>
  <li><strong>Order details page</strong> showing:
    <ul>
      <li>Order number and date</li>
      <li>Items ordered with quantities and prices</li>
      <li>Shipping address</li>
      <li>Payment method used</li>
      <li>Total amount paid</li>
      <li>Current order status</li>
    </ul>
  </li>
  <li><strong>Track order status</strong> in real-time with tracking map</li>
  <li><strong>Download invoices</strong> - PDF format</li>
  <li><strong>Cancel orders</strong>:
    <ul>
      <li>Within 1 hour of placement</li>
      <li>Only if order not yet shipped</li>
      <li>Automatic refund processing</li>
    </ul>
  </li>
  <li><strong>Return/refund requests</strong>:
    <ul>
      <li>Within 30 days of delivery</li>
      <li>Upload images of product/packaging</li>
      <li>Select return reason</li>
      <li>Track return status</li>
    </ul>
  </li>
  <li><strong>Leave product reviews</strong> - After delivery confirmation</li>
  <li><strong>Reorder</strong> - Quick reorder of previous orders</li>
</ul>
<h2>Order Status Workflow</h2>
<p>The system should track following order statuses with timestamps:</p>
<ol>
  <li><strong>Pending</strong> - Order placed, awaiting payment confirmation</li>
  <li><strong>Confirmed</strong> - Payment received, order confirmed</li>
  <li><strong>Processing</strong> - Order being prepared for shipment</li>
  <li><strong>Shipped</strong> - Order dispatched with tracking number</li>
  <li><strong>Out for Delivery</strong> - With delivery partner</li>
  <li><strong>Delivered</strong> - Successfully delivered to customer</li>
  <li><strong>Cancelled</strong> - Order cancelled by customer or system</li>
  <li><strong>Refunded</strong> - Payment refunded to customer</li>
  <li><strong>Return Initiated</strong> - Customer requested return</li>
  <li><strong>Returned</strong> - Product returned to warehouse</li>
</ol>
<h2>Notification System</h2>
<p>Customers should receive automated notifications via email and SMS for:</p>
<ul>
  <li>Order confirmation with details</li>
  <li>Payment confirmation</li>
  <li>Order shipped with tracking link</li>
  <li>Out for delivery with estimated time</li>
  <li>Delivery confirmation</li>
  <li>Cancellation confirmation</li>
  <li>Refund processed</li>
</ul>
<p><strong>Estimated Effort:</strong> 45 hours</p>', 1, 1, 1, 4, 1, NOW(), NOW()),

(14, 'Admin Dashboard', '<h1>Admin Dashboard Requirements</h1>
<h2>Dashboard Overview</h2>
<p>The admin dashboard should provide real-time insights with:</p>
<ul>
  <li><strong>Sales Metrics</strong>:
    <ul>
      <li>Total sales today/this week/this month</li>
      <li>Revenue charts (line graph, bar chart)</li>
      <li>Comparison with previous periods</li>
      <li>Average order value</li>
    </ul>
  </li>
  <li><strong>Order Statistics</strong>:
    <ul>
      <li>Total orders by status (pie chart)</li>
      <li>Pending orders requiring attention</li>
      <li>Return/refund requests</li>
      <li>Orders by shipping method</li>
    </ul>
  </li>
  <li><strong>User Analytics</strong>:
    <ul>
      <li>Total registered users</li>
      <li>New users this month</li>
      <li>Active users (logged in last 7 days)</li>
      <li>User growth chart</li>
    </ul>
  </li>
  <li><strong>Product Insights</strong>:
    <ul>
      <li>Top 10 selling products</li>
      <li>Low stock alerts</li>
      <li>Out of stock items</li>
      <li>Products with most reviews</li>
    </ul>
  </li>
  <li><strong>Recent Activities Log</strong> - Last 20 system activities</li>
</ul>
<h2>Management Capabilities</h2>
<h3>User Management</h3>
<ul>
  <li>View all users with pagination and search</li>
  <li>Filter by user type, registration date, status</li>
  <li>Edit user details</li>
  <li>Suspend/unsuspend accounts</li>
  <li>Delete users (with confirmation)</li>
  <li>View user order history</li>
  <li>Send email to users</li>
</ul>
<h3>Product Management</h3>
<ul>
  <li>Add new products with:
    <ul>
      <li>Basic information (name, SKU, description)</li>
      <li>Pricing (regular, sale, bulk discounts)</li>
      <li>Images upload (multiple)</li>
      <li>Inventory (stock quantity, low stock threshold)</li>
      <li>Categories and tags</li>
      <li>Specifications</li>
      <li>SEO metadata</li>
    </ul>
  </li>
  <li>Edit existing products</li>
  <li>Bulk actions (delete, update prices, change categories)</li>
  <li>Import/export products (CSV, Excel)</li>
  <li>Duplicate products</li>
</ul>
<h3>Category Management</h3>
<ul>
  <li>Create/edit/delete categories</li>
  <li>Hierarchical category structure</li>
  <li>Category images and descriptions</li>
  <li>SEO settings for categories</li>
  <li>Featured categories</li>
</ul>
<h3>Order Management</h3>
<ul>
  <li>View all orders with advanced filters</li>
  <li>Update order status manually</li>
  <li>Add tracking numbers</li>
  <li>Generate invoices and packing slips</li>
  <li>Process refunds</li>
  <li>Handle return requests</li>
  <li>Order notes and internal comments</li>
</ul>
<h3>Content Management</h3>
<ul>
  <li>Manage static pages (About Us, Contact, FAQ, etc.)</li>
  <li>Create/edit homepage banners</li>
  <li>Promotional sections management</li>
  <li>Email templates customization</li>
  <li>Blog posts (if applicable)</li>
</ul>
<h3>Reports & Analytics</h3>
<ul>
  <li><strong>Sales Reports</strong>:
    <ul>
      <li>Sales by date range</li>
      <li>Sales by product/category</li>
      <li>Sales by payment method</li>
      <li>Export to PDF/Excel</li>
    </ul>
  </li>
  <li><strong>Inventory Reports</strong>:
    <ul>
      <li>Stock levels</li>
      <li>Low stock items</li>
      <li>Product performance</li>
    </ul>
  </li>
  <li><strong>Customer Reports</strong>:
    <ul>
      <li>Top customers</li>
      <li>Customer lifetime value</li>
      <li>New vs returning customers</li>
    </ul>
  </li>
</ul>
<h3>Settings & Configuration</h3>
<ul>
  <li><strong>General Settings</strong>:
    <ul>
      <li>Site name, logo, favicon</li>
      <li>Contact information</li>
      <li>Social media links</li>
      <li>Timezone and currency</li>
    </ul>
  </li>
  <li><strong>Payment Gateway Configuration</strong>:
    <ul>
      <li>Enable/disable payment methods</li>
      <li>API keys and credentials</li>
      <li>Test mode toggle</li>
    </ul>
  </li>
  <li><strong>Shipping Configuration</strong>:
    <ul>
      <li>Shipping zones and rates</li>
      <li>Free shipping rules</li>
      <li>Carrier integrations</li>
    </ul>
  </li>
  <li><strong>Tax Settings</strong>:
    <ul>
      <li>Tax rates by location</li>
      <li>Tax classes</li>
    </ul>
  </li>
  <li><strong>Email Settings</strong>:
    <ul>
      <li>SMTP configuration</li>
      <li>Email templates</li>
      <li>Notification preferences</li>
    </ul>
  </li>
</ul>
<p><strong>Estimated Effort:</strong> 80 hours</p>', 1, 1, 1, 5, 1, NOW(), NOW()),

(14, 'Security & Performance', '<h1>Security & Performance Requirements</h1>
<h2>Security Requirements</h2>
<ul>
  <li><strong>Data Protection</strong>:
    <ul>
      <li>All sensitive data encrypted at rest</li>
      <li>SSL/TLS for all communications</li>
      <li>PCI DSS compliance for payment data</li>
      <li>GDPR compliance for EU customers</li>
    </ul>
  </li>
  <li><strong>Access Control</strong>:
    <ul>
      <li>Role-based access control (RBAC)</li>
      <li>Strong password policies</li>
      <li>Session timeout after inactivity</li>
      <li>IP whitelisting for admin panel</li>
    </ul>
  </li>
  <li><strong>Vulnerability Protection</strong>:
    <ul>
      <li>SQL injection prevention</li>
      <li>XSS (Cross-Site Scripting) protection</li>
      <li>CSRF (Cross-Site Request Forgery) tokens</li>
      <li>Rate limiting on APIs</li>
      <li>DDoS protection</li>
    </ul>
  </li>
  <li><strong>Audit & Monitoring</strong>:
    <ul>
      <li>Activity logs for all admin actions</li>
      <li>Failed login attempt tracking</li>
      <li>Suspicious activity alerts</li>
      <li>Regular security audits</li>
    </ul>
  </li>
</ul>
<h2>Performance Requirements</h2>
<ul>
  <li><strong>Page Load Speed</strong>:
    <ul>
      <li>Homepage loads in under 2 seconds</li>
      <li>Product pages load in under 3 seconds</li>
      <li>Search results in under 1 second</li>
    </ul>
  </li>
  <li><strong>Optimization Techniques</strong>:
    <ul>
      <li>Image lazy loading</li>
      <li>CDN for static assets</li>
      <li>Database query optimization</li>
      <li>Redis caching for frequently accessed data</li>
      <li>Minification of CSS/JS</li>
      <li>Gzip compression</li>
    </ul>
  </li>
  <li><strong>Scalability</strong>:
    <ul>
      <li>Support for 10,000 concurrent users</li>
      <li>Horizontal scaling capability</li>
      <li>Load balancing</li>
      <li>Database replication</li>
    </ul>
  </li>
  <li><strong>Uptime</strong>:
    <ul>
      <li>99.9% uptime SLA</li>
      <li>Automated failover</li>
      <li>Health monitoring</li>
      <li>Backup and disaster recovery</li>
    </ul>
  </li>
</ul>
<p><strong>Estimated Effort:</strong> 30 hours</p>', 1, 1, 1, 6, 1, NOW(), NOW());
