<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bus Ticket Booking | Modern Transit</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { fontFamily: { sans: ['Poppins', 'sans-serif'], }, colors: { primary: '#ff5a5f', secondary: '#484848' } } }
        }
    </script>
    <style>
        body { font-family: 'Poppins', sans-serif; overflow-x: hidden; }
        .hero-section {
            background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?q=80&w=2069&auto=format&fit=crop');
            background-size: cover; background-position: center; background-attachment: fixed;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3); box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }
        .nav-scrolled { background-color: rgba(255, 255, 255, 0.95) !important; backdrop-filter: blur(10px); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .nav-scrolled .nav-link, .nav-scrolled .logo-text, .nav-scrolled .mobile-menu-btn { color: #333 !important; }
        .nav-link { position: relative; color: white; transition: color 0.3s ease; }
        .nav-link::after {
            content: ''; position: absolute; width: 0; height: 2px; bottom: -4px; left: 0; background-color: #ff5a5f; transition: width 0.3s ease;
        }
        .nav-link:hover::after { width: 100%; }
        .input-group { transition: all 0.3s ease; }
        .input-group:focus-within { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">
    <nav id="navbar" class="fixed w-full z-50 transition-all duration-300 bg-transparent py-4 text-white">
        <div class="container mx-auto px-6 lg:px-12 max-w-7xl flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold flex items-center gap-2 logo-text text-white transition-colors duration-300">
                <i class="fa-solid fa-bus text-primary"></i> Voyage
            </a>
            <div class="hidden md:flex space-x-8 items-center font-medium">
                <a href="#" class="nav-link">Home</a>
                <a href="#about" class="nav-link">About</a>
                <a href="#testimonials" class="nav-link">Reviews</a>
                <a href="#" class="nav-link">Login</a>
                <a href="#" class="bg-primary hover:bg-red-600 text-white px-6 py-2 rounded-full transition-transform transform hover:scale-105 shadow-md">Contact Us</a>
            </div>
            <div class="md:hidden">
                <button class="mobile-menu-btn text-2xl focus:outline-none transition-colors"><i class="fa-solid fa-bars"></i></button>
            </div>
        </div>
    </nav>

    <header class="relative hero-section h-screen min-h-[600px] flex items-center justify-center pt-20">
        <div class="container mx-auto px-4 z-10 text-center flex flex-col items-center justify-center w-full">
            <div data-aos="fade-down" data-aos-duration="1000">
                <h1 class="text-5xl md:text-7xl font-extrabold text-white mb-4 tracking-tight drop-shadow-lg">BOOK INTERDISTRICT BUSES</h1>
                <p class="text-xl md:text-2xl text-gray-200 mb-12 font-light tracking-wide drop-shadow-md">Fast, Safe & Comfortable Bus Travel</p>
            </div>
            
            <div class="glass-card md:bg-white md:backdrop-filter-none w-full max-w-5xl rounded-2xl md:rounded-full p-4 md:p-3 relative" data-aos="zoom-in" data-aos-delay="300" data-aos-duration="1000">
                <form action="search_bus.php" method="POST" class="flex flex-col md:flex-row gap-3 md:gap-0 items-center w-full">
                    <div class="input-group flex-1 w-full bg-white rounded-xl md:rounded-l-full md:rounded-r-none flex items-center px-4 py-3 md:py-3 border border-gray-200 focus-within:ring-2 focus-within:ring-primary focus-within:border-primary relative md:border-r-0 z-10">
                        <i class="fa-solid fa-location-dot text-gray-400 mr-3 text-lg w-5 text-center"></i>
                        <input type="text" name="source" placeholder="From City" required class="w-full outline-none text-gray-700 bg-transparent placeholder-gray-400 font-medium text-base">
                    </div>
                    <div class="hidden md:block w-px h-10 bg-gray-300 z-20"></div>
                    <div class="input-group flex-1 w-full bg-white rounded-xl md:rounded-none flex items-center px-4 py-3 md:py-3 border border-gray-200 md:border-l-0 md:border-r-0 focus-within:ring-2 focus-within:ring-primary focus-within:border-primary relative z-10">
                        <i class="fa-solid fa-map-location-dot text-gray-400 mr-3 text-lg w-5 text-center"></i>
                        <input type="text" name="destination" placeholder="To City" required class="w-full outline-none text-gray-700 bg-transparent placeholder-gray-400 font-medium text-base">
                    </div>
                    <div class="hidden md:block w-px h-10 bg-gray-300 z-20"></div>
                    <div class="input-group flex-1 w-full bg-white rounded-xl md:rounded-none flex items-center px-4 py-3 md:py-3 border border-gray-200 md:border-l-0 md:border-r-0 focus-within:ring-2 focus-within:ring-primary focus-within:border-primary relative z-10">
                        <i class="fa-solid fa-calendar-days text-gray-400 mr-3 text-lg w-5 text-center"></i>
                        <input type="text" placeholder="Date of Journey" onfocus="(this.type='date')" onblur="(this.type='text')" class="w-full outline-none text-gray-700 bg-transparent placeholder-gray-400 font-medium text-base cursor-pointer">
                    </div>
                    <div class="hidden md:block w-px h-10 bg-gray-300 z-20"></div>
                    <div class="input-group flex-1 w-full bg-white rounded-xl md:rounded-none flex items-center px-4 py-3 md:py-3 border border-gray-200 md:border-l-0 focus-within:ring-2 focus-within:ring-primary focus-within:border-primary md:border-r-0 relative z-10">
                        <i class="fa-solid fa-calendar-plus text-gray-400 mr-3 text-lg w-5 text-center"></i>
                        <input type="text" placeholder="Return Date" onfocus="(this.type='date')" onblur="(this.type='text')" class="w-full outline-none text-gray-700 bg-transparent placeholder-gray-400 font-medium text-base cursor-pointer">
                    </div>
                    <button type="submit" class="w-full md:w-auto bg-primary hover:bg-red-600 text-white font-semibold py-4 md:py-4 px-8 rounded-xl md:rounded-r-full md:rounded-l-none transition-all duration-300 flex items-center justify-center gap-2 whitespace-nowrap shadow-md focus:outline-none focus:ring-4 focus:ring-red-300 z-10 md:-ml-1 m-0">
                        Search Buses <i class="fa-solid fa-arrow-right ml-1"></i>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <section id="about" class="py-24 bg-white overflow-hidden">
        <div class="container mx-auto px-6 max-w-7xl">
            <div class="flex flex-col lg:flex-row items-center gap-16">
                <div class="flex-1 w-full" data-aos="fade-right" data-aos-duration="1000">
                    <div class="relative rounded-2xl overflow-hidden shadow-2xl group">
                        <img src="https://images.unsplash.com/photo-1570125909232-eb263c188f7e?q=80&w=2071&auto=format&fit=crop" alt="Luxury Travel Bus" class="w-full h-[450px] object-cover transition-transform duration-700 group-hover:scale-105">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent flex items-end p-8">
                            <div class="text-white">
                                <p class="font-bold text-xl drop-shadow-md">Premium Fleet</p>
                                <p class="opacity-90 drop-shadow-md">Your comfort is our priority</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex-1" data-aos="fade-left" data-aos-duration="1000">
                    <span class="inline-block py-1 px-3 rounded-full bg-red-100 text-primary font-semibold text-sm tracking-widest uppercase mb-4">About Our Service</span>
                    <h3 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6 leading-tight">We Provide The Best Bus Experience For You</h3>
                    <p class="text-gray-600 text-lg mb-8 leading-relaxed">Experience the ultimate comfort and safety with our premium interdistrict bus services. We connect major cities with a modern fleet equipped with top-notch amenities, ensuring your journey is as smooth as possible.</p>
                    <ul class="space-y-4 mb-10">
                        <li class="flex items-center text-gray-800 font-medium"><span class="w-10 h-10 rounded-full bg-green-100 text-green-600 flex items-center justify-center mr-4"><i class="fa-solid fa-check"></i></span> Premium comfortable and recliner seating</li>
                        <li class="flex items-center text-gray-800 font-medium"><span class="w-10 h-10 rounded-full bg-green-100 text-green-600 flex items-center justify-center mr-4"><i class="fa-solid fa-map-location-dot"></i></span> Live GPS tracking & top-tier security</li>
                        <li class="flex items-center text-gray-800 font-medium"><span class="w-10 h-10 rounded-full bg-green-100 text-green-600 flex items-center justify-center mr-4"><i class="fa-solid fa-clock"></i></span> On-time departure & arrival guarantees</li>
                    </ul>
                    <a href="#" class="inline-block bg-gray-900 hover:bg-gray-800 text-white font-medium py-3 px-8 rounded-full transition-transform duration-300 transform hover:-translate-y-1 shadow-lg">Learn More</a>
                </div>
            </div>
        </div>
    </section>

    <section id="testimonials" class="py-24 bg-gray-50 relative">
        <div class="container mx-auto px-6 max-w-7xl relative z-10 text-center">
            <div data-aos="fade-up" data-aos-duration="800">
                <span class="inline-block py-1 px-3 rounded-full bg-red-100 text-primary font-semibold text-sm tracking-widest uppercase mb-4">Testimonials</span>
                <h3 class="text-4xl font-bold text-gray-900 mb-16">What Our Passengers Say</h3>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-xl transition-all duration-300 text-left transform hover:-translate-y-2" data-aos="fade-up" data-aos-delay="100" data-aos-duration="800">
                    <i class="fa-solid fa-quote-left text-4xl text-gray-100 mb-6 block"></i>
                    <p class="text-gray-600 mb-8 leading-relaxed">"The booking process was incredibly smooth. The bus arrived right on time and the seats were exceptionally comfortable. Highly recommended!"</p>
                    <div class="flex items-center gap-4 mt-auto">
                        <img src="https://i.pravatar.cc/150?img=1" alt="User" class="w-12 h-12 rounded-full object-cover">
                        <div>
                            <h4 class="font-bold text-gray-900">Sarah Jenkins</h4>
                            <p class="text-xs text-yellow-400 mt-1"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-8 rounded-2xl shadow-lg border border-primary/20 hover:shadow-xl transition-all duration-300 text-left transform hover:-translate-y-2 relative" data-aos="fade-up" data-aos-delay="200" data-aos-duration="800">
                    <div class="absolute top-0 right-0 w-2 h-full bg-primary rounded-r-2xl"></div>
                    <i class="fa-solid fa-quote-left text-4xl text-gray-100 mb-6 block"></i>
                    <p class="text-gray-600 mb-8 leading-relaxed">"I travel frequently for business. This has become my go-to service for interdistrict trips. Safe, reliable, and very convenient with the online booking features."</p>
                    <div class="flex items-center gap-4 mt-auto">
                        <img src="https://i.pravatar.cc/150?img=11" alt="User" class="w-12 h-12 rounded-full object-cover">
                        <div>
                            <h4 class="font-bold text-gray-900">Michael Ross</h4>
                            <p class="text-xs text-yellow-400 mt-1"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-xl transition-all duration-300 text-left transform hover:-translate-y-2" data-aos="fade-up" data-aos-delay="300" data-aos-duration="800">
                    <i class="fa-solid fa-quote-left text-4xl text-gray-100 mb-6 block"></i>
                    <p class="text-gray-600 mb-8 leading-relaxed">"First class experience! The staff was friendly and the cleanliness of the bus was outstanding. I won't travel any other way now."</p>
                    <div class="flex items-center gap-4 mt-auto">
                        <img src="https://i.pravatar.cc/150?img=5" alt="User" class="w-12 h-12 rounded-full object-cover">
                        <div>
                            <h4 class="font-bold text-gray-900">Emily Chen</h4>
                            <p class="text-xs text-yellow-400 mt-1"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-regular fa-star"></i></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300 py-16">
        <div class="container mx-auto px-6 max-w-7xl grid md:grid-cols-2 lg:grid-cols-4 gap-10 lg:gap-8">
            <div>
                <a href="#" class="text-2xl font-bold flex items-center gap-2 text-white mb-6"><i class="fa-solid fa-bus text-primary"></i> Voyage</a>
                <p class="text-gray-400 mb-6 leading-relaxed">Making your interdistrict travel comfortable, affordable, and safe. Your adventure starts here.</p>
                <div class="flex gap-4">
                    <a href="#" class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center hover:bg-primary transition-colors text-white"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center hover:bg-primary transition-colors text-white"><i class="fa-brands fa-twitter"></i></a>
                    <a href="#" class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center hover:bg-primary transition-colors text-white"><i class="fa-brands fa-instagram"></i></a>
                </div>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-6 uppercase tracking-wider text-sm">Quick Links</h4>
                <ul class="space-y-3">
                    <li><a href="#" class="text-gray-400 hover:text-primary transition-colors">Home</a></li>
                    <li><a href="#about" class="text-gray-400 hover:text-primary transition-colors">About</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-primary transition-colors">Destinations</a></li>
                    <li><a href="#testimonials" class="text-gray-400 hover:text-primary transition-colors">Reviews</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-6 uppercase tracking-wider text-sm">Support</h4>
                <ul class="space-y-3">
                    <li><a href="#" class="text-gray-400 hover:text-primary transition-colors">FAQ</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-primary transition-colors">Terms of Service</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-primary transition-colors">Privacy Policy</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-6 uppercase tracking-wider text-sm">Contact Us</h4>
                <ul class="space-y-4">
                    <li class="flex items-start"><i class="fa-solid fa-phone mt-1 w-6 text-primary"></i> <span class="text-gray-400">+1 234 567 890</span></li>
                    <li class="flex items-start"><i class="fa-solid fa-envelope mt-1 w-6 text-primary"></i> <span class="text-gray-400">info@voyagetransit.com</span></li>
                    <li class="flex items-start"><i class="fa-solid fa-location-dot mt-1 w-6 text-primary"></i> <span class="text-gray-400">123 Travel Avenue, New York, NY</span></li>
                </ul>
            </div>
        </div>
        <div class="mt-16 pt-8 border-t border-gray-800 text-center text-gray-500 text-sm">
            <p>&copy; 2026 Voyage Bus Ticket Booking. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            AOS.init({ once: true, offset: 50 });
            const navbar = document.getElementById('navbar');
            window.addEventListener('scroll', () => {
                if (window.scrollY > 50) {
                    navbar.classList.add('nav-scrolled');
                    navbar.classList.remove('bg-transparent', 'py-4', 'text-white');
                    navbar.classList.add('py-3');
                } else {
                    navbar.classList.remove('nav-scrolled', 'py-3');
                    navbar.classList.add('bg-transparent', 'py-4', 'text-white');
                }
            });
        });
    </script>
</body>
</html>