<?php
include_once('../../../admin/config/config.php');
include_once('../../../admin/config/checklogin.php');
require_once('../../../admin/inc/alert.php');

// Fetch all data from site_settings table
$query = "SELECT * FROM site_settings LIMIT 1";
$result = $mysqli->query($query);
$settings = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($settings['site_name']); ?> | Main Page</title>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Dynamically load favicon if available -->
    <?php if (!empty($settings['site_favicon'])): ?>
        <link rel="icon" type="image/png" href="../../../admin/dist/img/logos/<?php echo htmlspecialchars($settings['site_favicon']); ?>">
    <?php endif; ?>

    <!-- Import Links -->
    <?php require('./inc/links.php'); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- Bootstrap CSS -->
</head>


<body>
    <!-- Navigation -->
    <?php require('./inc/nav.php'); ?>

    <!-- SLIDER -->
<div class="container-fluid px-lg-0 mt-0">
    <div class="swiper swiper-container">
        <div class="swiper-wrapper">
            <?php if (!empty($settings['carousel1'])): ?>
            <div class="swiper-slide">
                <div class="image-container">
                    <img src="../../../admin/dist/img/carousels/<?php echo htmlspecialchars($settings['carousel1']); ?>" class="w-100 d-block" alt="Carousel 1">
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($settings['carousel2'])): ?>
            <div class="swiper-slide">
                <div class="image-container">
                    <img src="../admin/dist/img/carousels/<?php echo htmlspecialchars($settings['carousel2']); ?>" class="w-100 d-block" alt="Carousel 2">
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($settings['carousel3'])): ?>
            <div class="swiper-slide">
                <div class="image-container">
                    <img src="../admin/dist/img/carousels/<?php echo htmlspecialchars($settings['carousel3']); ?>" class="w-100 d-block" alt="Carousel 3">
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

    <div class="container availabilityCheck" style="background-color: #fff; padding: 25px; border-radius: 10px; margin-top: -50px;">
        <div class="col-lg-8 d-flex align-items-center justify-content-between">

            <div class="col-lg-3 me-2">
                <div class="mb-2">
                    <label class="form-label someText m-0">Check In</label>
                    <input type="date" name="check_in" class="form-control someText shadow-none" required>
                </div>
            </div>

            <div class="col-lg-3 me-2">
                <div class="mb-2">
                    <label class="form-label someText m-0">Check Out</label>
                    <input type="date" name="check_out" class="form-control someText shadow-none" required>
                </div>
            </div>

            <div class="col-lg-3 me-2">
                <div class="mb-2">
                    <label class="form-label someText m-0">Adult</label>
                    <input type="number" name="adult" class="form-control someText shadow-none" required>
                </div>
            </div>

            <div class="col-lg-3 me-2">
                <div class="mb-2">
                    <label class="form-label someText m-0">Child</label>
                    <input type="number" name="child" class="form-control someText shadow-none" required>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="mb-2">
                    <button class="btn btn-primary someText btnAddCategory">Check Availability</button>
                </div>
            </div>



        </div>
    </div>

    <!-- Home -->
    <div class="row">
        <div class="container py-5" style="padding: 20px;">
            <div class="col-lg-8 m-auto text-center py-5">
                <p class="miniTitle">WELCOME</p>
                <h5 class="bigTitle">LUXE HAVEN HOTEL PH</h5>
                <p class="contentPara"><?php echo htmlspecialchars($settings['site_welcome_text']); ?></p>
            </div>
        </div>
    </div>


    <!-- Our Rooms -->
    <div class="row" style="background-color:#f5f5f5;">
        <div class="container py-5" style="padding: 20px;">
            <div class="col-lg-8 m-auto text-center py-5">
                <p class="miniTitle">LOOKING FOR A PLACE TO STAY?</p>
                <h5 class="bigTitle mb-5">OUR ROOMS</h5>

                <div class="container">
                    <div class="col-lg-12 d-flex justify-content-around" style="flex-wrap:wrap;">


                        <?php
                        $ret = "SELECT * FROM  rooms WHERE room_status = 'Available' ORDER BY RAND() LIMIT 6";
                        $stmt = $mysqli->prepare($ret);
                        $stmt->execute(); //ok
                        $res = $stmt->get_result();

                        while ($row1 = $res->fetch_object()) {
                        ?>

                            <div class="card mb-4" style="width: 18rem;">
                                <img src="../admin/dist/img/<?php echo $row1->room_picture ?>" class="card-img-top" style="height: 200px; object-fit:cover;">
                                <div class="card-body">
                                    <p class="miniTitle"><?php echo $row1->room_category ?></p>
                                    <h5 class="cardRoomTitle"><?php echo $row1->room_name ?></h5>
                                    <p class="cardRoomDescription"><?php echo $row1->room_description ?></p>

                                    <div class="container d-flex justify-content-center">
                                        <p class="cardRoomDescription"><b>ADULTS: </b> <?php echo $row1->room_adult ?></p>

                                        &nbsp; &nbsp;

                                        <p class="cardRoomDescription"><b>CHILD: </b> <?php echo $row1->room_child ?></p>
                                    </div>






                                </div>
                                <div class="mb-3 mt-0">
                                    <a href="room_details.php?room_id=<?php echo $row1->room_id ?>" class="btn btn-primary btnAddCategory someText">Check More Details</a>
                                </div>
                            </div>

                        <?php } ?>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- About Us -->
<section id="aboutus" class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <p class="miniTitle">HOW WE STARTED</p>
            <h2 class="bigTitle">ABOUT US</h2>
        </div>

        <div class="row align-items-center mb-5">
            <div class="col-lg-6">
                <img src="dist/img/meeting.jpg" class="img-fluid rounded shadow" alt="Our Beginnings">
            </div>
            <div class="col-lg-6">
                <h3 class="bigTitle mt-3">Our Beginnings</h3>
                <p class="contentPara">
                    Luxe Haven started as a small boutique hotel catering to discerning guests seeking an intimate and personalized stay. Over the years, our commitment to excellence earned us a reputation as one of the most sought-after destinations for both leisure and business travelers.
                </p>
            </div>
        </div>

        <div class="row align-items-center mb-5 flex-lg-row-reverse">
            <div class="col-lg-6">
                <img src="dist/img/image.jpg" class="img-fluid rounded shadow" alt="Our Growth and Achievements">
            </div>
            <div class="col-lg-6">
                <h3 class="bigTitle">Our Growth and Achievements</h3>
                <p class="contentPara">
                    As our guests’ trust and loyalty grew, so did our vision. Luxe Haven expanded its offerings, introducing state-of-the-art facilities, luxurious suites, fine dining restaurants, and wellness amenities. By 2015, we proudly became a premier destination for international travelers, hosting memorable weddings, corporate events, and once-in-a-lifetime celebrations.
                </p>
            </div>
        </div>

        <div class="row align-items-center">
            <div class="col-lg-6">
                <img src="dist/img/slide 2.jpg" class="img-fluid rounded shadow" alt="Our Philosophy and Promise">
            </div>
            <div class="col-lg-6">
                <h3 class="bigTitle">Our Philosophy and Promise</h3>
                <p class="contentPara">
                    At Luxe Haven, we believe in creating moments that matter. We combine modern elegance with touches of traditional Filipino culture, showcasing the beauty of the Philippines while offering unparalleled comfort. We strive to provide not just a stay, but an experience. Whether you’re here for a relaxing escape, a business trip, or a special occasion, Luxe Haven Hotel PH promises exceptional service, luxurious accommodations, and unforgettable memories.
                </p>
            </div>
        </div>
    </div>
</section>


    <!-- Services -->
    <div class="row" id="services" style="background-color:#f5f5f5;">
        <div class="container py-5" style="padding: 20px;">
            <div class="col-lg-8 m-auto text-center py-5">
                <p class="miniTitle">WHAT WE DO?</p>
                <h5 class="bigTitle">OUR SERVICES</h5>

                <div class="container">
                    <div class="col-lg-12 d-flex justify-content-around" style="flex-wrap:wrap;">


                        <?php
                        $ret = "SELECT * FROM  room_services WHERE service_status = 'Available' ORDER BY RAND() LIMIT 6";
                        $stmt = $mysqli->prepare($ret);
                        $stmt->execute(); //ok
                        $res = $stmt->get_result();

                        while ($row1 = $res->fetch_object()) {
                        ?>

                            <div class="card mb-4" style="width: 18rem;">
                                <img src="../admin/dist/img/<?php echo $row1->service_picture ?>" class="card-img-top" style="height: 180px;">
                                <div class="card-body">
                                    <h5 class="cardRoomTitle mt-2 mb-1"><?php echo $row1->service_name ?></h5>
                                    <p class="cardRoomDescription"><?php echo $row1->service_description ?></p>
                                </div>
                            </div>

                        <?php } ?>

                    </div>
                </div>

            </div>
        </div>
    </div>
<!-- Contact Us -->
<section id="contactus" class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <p class="miniTitle">We are located at - wait, you should know that!</p>
            <h2 class="bigTitle">Contact Us</h2>
        </div>
        <div class="row g-4 align-items-center">
            <!-- Google Map -->
            <div class="col-lg-6">
                <div class="rounded overflow-hidden" style="height: 400px;">
                    <?php echo $settings['site_iframe_address']; ?>
                </div>
            </div>


            <!-- Inquiry Form -->
            <div class="col-lg-6">
                <div class="p-4 bg-white shadow rounded" style="height: 100%;">
                    <h4 class="bigTitle">Send Us a Message</h4>
                    <form id="inquiryForm" action="send_inquiry.php" method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label fw-medium">Your Name</label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Enter your name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label fw-medium">Your Email</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label fw-medium">Your Inquiry</label>
                            <textarea class="form-control" id="message" name="message" rows="4" placeholder="Enter your message" required></textarea>
                        </div>
                        <div class="mb-2 d-grid mt-3">
                            <button type="submit" name="send_inquiry" class="btn btn-primary btnAddCategory someText">Send Inquiry</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>


<script>
document.getElementById("inquiryForm").addEventListener("submit", function(event) {
    event.preventDefault(); 

    if (confirm("Are you sure you want to submit your inquiry?")) {
        let formData = new FormData(this);

        fetch("send_inquiry.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                alert("Your inquiry has been sent successfully!");
                document.getElementById("inquiryForm").reset();
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => console.error("Error:", error));
    }
});
</script>










    <!-- SWIPER CDN Script -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="https://unpkg.com/swiper@7/swiper-bundle.min.js"></script>

    <script>
        var swiper = new Swiper(".swiper-container", {
            spaceBetween: 30,
            effect: "fade",
            loop: false,
            autoplay: {
                delay: 6000,
                disableOnInteraction: false,
            },
        });

        var swiper = new Swiper(".swiper-container1", {
            spaceBetween: 30,
            effect: "fade",
            loop: true,
            effect: "swiper-centered",
            centeredSlides: true,
            slidesPerView: "auto",
            slidesPerView: "3",
            autoplay: {
                delay: 6000,
                disableOnInteraction: false,
            },
            pagination: {
                el: ".swiper-pagination",
            },
        });
    </script>



<script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelector(".btnAddCategory").addEventListener("click", function () {
        let checkIn = document.querySelector("input[name='check_in']").value;
        let checkOut = document.querySelector("input[name='check_out']").value;
        let adult = document.querySelector("input[name='adult']").value;
        let child = document.querySelector("input[name='child']").value;

        // Validate input fields
        if (!checkIn || !checkOut || adult < 1) {
            Swal.fire({
                icon: "error",
                title: "Invalid Input",
                text: "Please fill all fields correctly.",
            });
            return;
        }

        // Show loading SweetAlert
        Swal.fire({
            title: "Searching for availability...",
            text: "Please wait while we check available rooms.",
            icon: "info",
            showConfirmButton: false,
            allowOutsideClick: false,
            timer: 2000, 
        });

        // Delay before sending AJAX request
        setTimeout(() => {
            fetch("check_availability.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `check_in=${checkIn}&check_out=${checkOut}&adult=${adult}&child=${child}`,
            })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    Swal.fire({
                        icon: "success",
                        title: "Rooms Available!",
                        text: `We found ${data.room_count} available rooms.`,
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "No Rooms Available",
                        text: "Sorry, no rooms match your criteria.",
                    });
                }
            })
            .catch((error) => {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Something went wrong. Please try again later.",
                });
            });
        }, 2000); 
    });
});
</script>




</body>


<footer style="background-color: <?php echo $settings['site_primary_color']; ?>; color: white;">
    <div class="footer-container">
        <!-- Logo -->
        <div class="footer-logo">
            <img src="../admin/dist/img/logos/<?php echo $settings['site_logo']; ?>" alt="<?php echo $settings['site_name']; ?>">
        </div>

        <!-- Quick Links -->
        <div class="footer-links">
            <h4>Quick Links</h4>
            <ul>
                <li><a href="#">Home</a></li>
                <li><a href="index.php#aboutus">About Us</a></li>
                <li><a href="index.php#services">Services</a></li>
                <li><a href="index.php#contactus">Contact</a></li>
            </ul>
        </div>

        <!-- Contact Information -->
        <div class="footer-contact">
            <h4>Contact</h4>
            <p><i class="fas fa-envelope"></i> <a href="mailto:<?php echo $settings['site_email']; ?>"><?php echo $settings['site_email']; ?></a></p>
            <p><i class="fas fa-phone"></i> <a href="tel:<?php echo $settings['site_contact']; ?>"><?php echo $settings['site_contact']; ?></a></p>
        </div>

        <!-- Social Media -->
        <div class="footer-social">
            <h4>Follow Us</h4>
            <div class="social-icons">
                <a href="https://www.facebook.com/" target="_blank"><i class="fab fa-facebook-f"></i></a>
                <a href="https://twitter.com/" target="_blank"><i class="fab fa-twitter"></i></a>
                <a href="https://www.instagram.com/" target="_blank"><i class="fab fa-instagram"></i></a>
            </div>
        </div>


        <!-- Subscribe Section -->
        <div class="footer-subscribe">
            <h4>Subscribe to Our Newsletter</h4>
            <form action="subscribe.php" method="POST">
                <input type="email" name="email" placeholder="Enter your email" required>
                <button type="submit">Subscribe</button>
            </form>
        </div>
    </div>

    <!-- Footer Bottom -->
    <div class="footer-bottom">
        <p>&copy; <?php echo date("Y"); ?> <?php echo $settings['site_name']; ?>. All Rights Reserved.</p>
    </div>
</footer>

<style>
/* Footer Styling */
footer {
    padding: 40px 20px;
    text-align: center;
    font-family: 'Poppins', sans-serif;
}

/* Footer Container */
.footer-container {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    max-width: 1200px;
    margin: auto;
    text-align: left;
    gap: 30px;
}

/* Logo */
.footer-logo img {
    max-width: 140px;
}

/* Sections */
.footer-links, .footer-contact, .footer-social, .footer-subscribe {
    flex: 1;
    min-width: 220px;
}

/* Headings */
.footer-links h4, .footer-contact h4, .footer-social h4, .footer-subscribe h4 {
    margin-bottom: 12px;
    font-size: 18px;
    font-weight: bold;
    text-transform: uppercase;
}

/* Quick Links */
.footer-links ul {
    list-style: none;
    padding: 0;
}

.footer-links ul li {
    margin-bottom: 8px;
}

.footer-links ul li a {
    color: white;
    text-decoration: none;
    font-size: 14px;
    transition: color 0.3s ease;
}

.footer-links ul li a:hover {
    color: <?php echo $settings['site_hover_color']; ?>;
}

/* Contact Info */
.footer-contact p {
    margin: 8px 0;
    font-size: 14px;
}

.footer-contact i {
    margin-right: 8px;
    color: white;
}

.footer-contact a {
    color: white;
    text-decoration: none;
    transition: color 0.3s ease;
}

.footer-contact a:hover {
    color: <?php echo $settings['site_hover_color']; ?>;
}

/* Social Icons */
.footer-social .social-icons {
    display: flex;
    gap: 12px;
    justify-content: flex-start;
}

.footer-social .social-icons a {
    color: white;
    font-size: 18px;
    transition: color 0.3s ease, transform 0.3s ease;
}

.footer-social .social-icons a:hover {
    color: <?php echo $settings['site_hover_color']; ?>;
    transform: scale(1.1);
}

/* Subscribe Section */
.footer-subscribe form {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.footer-subscribe input {
    padding: 10px;
    border: none;
    border-radius: 5px;
    font-size: 14px;
    width: 100%;
}

.footer-subscribe button {
    padding: 10px;
    background-color: <?php echo $settings['site_bg_color']; ?>;
    color: black;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s ease;
}

.footer-subscribe button:hover {
    background-color: <?php echo $settings['site_bg_color']; ?>;
}

/* Footer Bottom */
.footer-bottom {
    margin-top: 20px;
    border-top: 1px solid #444;
    padding-top: 15px;
    font-size: 14px;
}
</style>



</html>