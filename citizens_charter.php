<script>
        // JavaScript for dropdown functionality and search
        document.addEventListener('DOMContentLoaded', function() {
            const dropdownItems = document.querySelectorAll('.dropdown-item');
            const searchInput = document.getElementById('searchInput');
            const filterButtons = document.querySelectorAll('.filter-btn');
            const resultsCount = document.getElementById('resultsCount');
            const clearSearchBtn = document.getElementById('clearSearch');
            const noResults = document.getElementById('noResults');
            const servicesContainer = document.getElementById('servicesContainer');
            const scrollTopBtn = document.getElementById('scrollTop');
            const categoryCards = document.querySelectorAll('.category-card');

            // Dropdown toggle functionality
            dropdownItems.forEach(item => {
                const header = item.querySelector('.dropdown-header');
                header.addEventListener('click', function() {
                    dropdownItems.forEach(otherItem => {
                        if (otherItem !== item && otherItem.classList.contains('active')) {
                            otherItem.classList.remove('active');
                        }
                    });
                    item.classList.toggle('active');
                });
            });

            // Search functionality
            function performSearch() {
                const searchTerm = searchInput.value.toLowerCase().trim();
                const activeFilter = document.querySelector('.filter-btn.active').dataset.category;
                let visibleCount = 0;

                dropdownItems.forEach(item => {
                    const itemText = item.textContent.toLowerCase();
                    const itemCategory = item.dataset.category;
                    const itemKeywords = item.dataset.keywords ? item.dataset.keywords.toLowerCase() : '';
                    
                    const matchesSearch = searchTerm === '' || 
                                        itemText.includes(searchTerm) || 
                                        itemKeywords.includes(searchTerm);
                    const matchesFilter = activeFilter === 'all' || itemCategory === activeFilter;

                    if (matchesSearch && matchesFilter) {
                        item.style.display = 'block';
                        visibleCount++;
                        
                        // Highlight search term
                        if (searchTerm !== '') {
                            highlightText(item, searchTerm);
                        } else {
                            removeHighlight(item);
                        }
                    } else {
                        item.style.display = 'none';
                    }
                });

                // Update results count
                resultsCount.textContent = visibleCount;
                
                // Show/hide no results message
                if (visibleCount === 0) {
                    noResults.style.display = 'block';
                    servicesContainer.style.display = 'none';
                } else {
                    noResults.style.display = 'none';
                    servicesContainer.style.display = 'block';
                }

                // Show/hide clear button
                clearSearchBtn.style.display = searchTerm !== '' ? 'inline' : 'none';
            }

            // Highlight matching text
            function highlightText(element, term) {
                const header = element.querySelector('.dropdown-header span');
                const originalText = header.getAttribute('data-original-text') || header.textContent;
                
                if (!header.hasAttribute('data-original-text')) {
                    header.setAttribute('data-original-text', originalText);
                }

                const regex = new RegExp(`(${term})`, 'gi');
                const highlightedText = originalText.replace(regex, '<span class="highlight">$1</span>');
                header.innerHTML = highlightedText;
            }

            // Remove highlight
            function removeHighlight(element) {
                const header = element.querySelector('.dropdown-header span');
                const originalText = header.getAttribute('data-original-text');
                if (originalText) {
                    header.textContent = originalText;
                }
            }

            // Search input event listener
            searchInput.addEventListener('input', performSearch);

            // Filter button functionality
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    performSearch();
                });
            });

            // Clear search
            clearSearchBtn.addEventListener('click', function() {
                searchInput.value = '';
                performSearch();
                searchInput.focus();
            });

            // Quick access category cards
            categoryCards.forEach(card => {
                card.addEventListener('click', function() {
                    const scrollTarget = this.dataset.scroll;
                    const targetElement = document.getElementById(scrollTarget);
                    
                    if (targetElement) {
                        // Clear any search/filter first
                        searchInput.value = '';
                        filterButtons.forEach(btn => btn.classList.remove('active'));
                        filterButtons[0].classList.add('active'); // Set to "All"
                        performSearch();
                        
                        // Scroll to element
                        targetElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        
                        // Open the dropdown
                        setTimeout(() => {
                            if (!targetElement.classList.contains('active')) {
                                targetElement.querySelector('.dropdown-header').click();
                            }
                            // Add a brief highlight effect
                            targetElement.style.boxShadow = '0 0 20px rgba(58, 155, 111, 0.5)';
                            setTimeout(() => {
                                targetElement.style.boxShadow = '';
                            }, 2000);
                        }, 500);
                    }
                });
            });

            // Scroll to top button functionality
            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 300) {
                    scrollTopBtn.classList.add('show');
                } else {
                    scrollTopBtn.classList.remove('show');
                }
            });

            scrollTopBtn.addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });

            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // Ctrl/Cmd + K to focus search
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    searchInput.focus();
                }
                
                // Escape to clear search
                if (e.key === 'Escape' && searchInput.value !== '') {
                    searchInput.value = '';
                    performSearch();
                }
            });

            // Add search placeholder animation
            const placeholders = [
                'Search for a service (e.g., "medical certificate")...',
                'Try searching "burial", "COVID", "employment"...',
                'Looking for vaccination certificates?',
                'Need a death certificate?',
                'Search by service name or keywords...'
            ];
            
            let placeholderIndex = 0;
            setInterval(() => {
                if (document.activeElement !== searchInput) {
                    placeholderIndex = (placeholderIndex + 1) % placeholders.length;
                    searchInput.placeholder = placeholders[placeholderIndex];
                }
            }, 4000);
        });
    </script><?php
require_once 'auth.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citizen's Charter - City Health Office of San Pablo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            /* San Pablo Green Color Palette */
            --primary-green: #1a5f3f;
            --primary-green-dark: #0f3d28;
            --primary-green-light: #2a7f5f;
            --accent-green: #3a9b6f;
            --light-green-bg: #e8f5f0;
            --white: #ffffff;
            --gray-text: #555555;
            --gray-light: #f8f9fa;
            --shadow: rgba(26, 95, 63, 0.1);
            --shadow-hover: rgba(26, 95, 63, 0.2);
        }

        body {
            background: var(--gray-light);
            color: var(--gray-text);
            min-height: 100vh;
            line-height: 1.6;
        }

        /* Header Styles */
        .main-header {
            position: relative;
            z-index: 100;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 40px;
            background: linear-gradient(135deg, #2d5f3f 0%, #1e4029 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border-bottom: 3px solid #4a8f5f;
        }

        .logo-container {
            display: flex;
            align-items: center;
        }

        .logo-img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            overflow: hidden;
            background: white;
            margin-right: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid #4a8f5f;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .logo-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .logo-container h1 {
            font-size: 2.2rem;
            font-weight: 700;
            color: white;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        nav ul {
            display: flex;
            gap: 20px;
            list-style: none;
            align-items: center;
        }

        nav ul li {
            display: inline-block;
        }

        nav ul li a {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: white;
            padding: 12px 18px;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
            position: relative;
        }

        nav ul li a:hover,
        nav ul li a.active {
            background: rgba(74, 143, 95, 0.4);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        nav ul li a i {
            font-size: 22px;
            margin-bottom: 6px;
        }

        nav ul li a span {
            font-size: 13px;
            font-weight: 600;
        }

        /* Page Header */
        .page-header {
            padding: 50px 40px;
            background: linear-gradient(135deg, var(--primary-green-light) 0%, var(--accent-green) 100%);
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .page-header h1 {
            font-size: 2.8rem;
            font-weight: 800;
            margin-bottom: 15px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
            position: relative;
            z-index: 2;
        }

        .page-header p {
            font-size: 1.15rem;
            opacity: 0.95;
            max-width: 700px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }

        /* Back Button */
        .back-button {
            display: inline-block;
            margin: 30px 40px 0;
            padding: 12px 24px;
            background: white;
            color: var(--primary-green);
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px var(--shadow);
        }

        .back-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px var(--shadow-hover);
            background: var(--light-green-bg);
        }

        .back-button i {
            margin-right: 8px;
        }

        /* Main Content */
        main {
            padding: 50px 40px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .content-column {
            background: white;
            border-radius: 15px;
            padding: 35px;
            box-shadow: 0 4px 12px var(--shadow);
            border: 1px solid #e0e0e0;
        }

        .column-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--light-green-bg);
        }

        .column-icon {
            width: 55px;
            height: 55px;
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--accent-green) 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 18px;
            box-shadow: 0 4px 12px var(--shadow);
        }

        .column-icon i {
            font-size: 24px;
            color: white;
        }

        .column-header h2 {
            font-size: 1.7rem;
            color: var(--primary-green-dark);
            font-weight: 700;
        }

        /* Search and Filter Section */
        .search-filter-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 12px var(--shadow);
            border: 1px solid #e0e0e0;
            margin-bottom: 30px;
        }

        .search-box {
            position: relative;
            margin-bottom: 20px;
        }

        .search-box input {
            width: 100%;
            padding: 15px 50px 15px 20px;
            border: 2px solid var(--light-green-bg);
            border-radius: 25px;
            font-size: 1rem;
            transition: all 0.3s ease;
            outline: none;
        }

        .search-box input:focus {
            border-color: var(--accent-green);
            box-shadow: 0 0 0 3px rgba(58, 155, 111, 0.1);
        }

        .search-box i {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--accent-green);
            font-size: 18px;
        }

        .filter-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .filter-label {
            font-weight: 600;
            color: var(--primary-green-dark);
            margin-right: 10px;
        }

        .filter-btn {
            padding: 8px 18px;
            border: 2px solid var(--light-green-bg);
            background: white;
            color: var(--gray-text);
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .filter-btn:hover {
            border-color: var(--accent-green);
            color: var(--accent-green);
        }

        .filter-btn.active {
            background: linear-gradient(135deg, var(--accent-green) 0%, var(--primary-green-light) 100%);
            color: white;
            border-color: var(--accent-green);
        }

        .results-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px 20px;
            background: var(--light-green-bg);
            border-radius: 10px;
        }

        .results-count {
            font-weight: 600;
            color: var(--primary-green-dark);
        }

        .clear-search {
            background: transparent;
            border: none;
            color: var(--accent-green);
            cursor: pointer;
            font-weight: 600;
            text-decoration: underline;
            transition: color 0.3s ease;
        }

        .clear-search:hover {
            color: var(--primary-green);
        }

        .no-results {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
            border: 2px dashed #ddd;
            margin-top: 20px;
        }

        .no-results i {
            font-size: 4rem;
            color: var(--accent-green);
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .no-results h3 {
            color: var(--primary-green);
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .no-results p {
            color: var(--gray-text);
        }

        /* Quick Access Categories */
        .quick-access {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .category-card {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--accent-green) 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px var(--shadow);
        }

        .category-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 16px var(--shadow-hover);
        }

        .category-card i {
            font-size: 2rem;
            margin-bottom: 10px;
            display: block;
        }

        .category-card h4 {
            font-size: 1.1rem;
            margin-bottom: 5px;
        }

        .category-card p {
            font-size: 0.85rem;
            opacity: 0.9;
        }

        /* Scroll to Top Button */
        .scroll-top {
            position: fixed;
            bottom: 100px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: var(--primary-green);
            color: white;
            border-radius: 50%;
            display: none;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px var(--shadow);
            z-index: 999;
        }

        .scroll-top:hover {
            background: var(--accent-green);
            transform: translateY(-3px);
        }

        .scroll-top.show {
            display: flex;
        }

        /* Highlight matched text */
        .highlight {
            background-color: #ffeb3b;
            padding: 2px 4px;
            border-radius: 3px;
            font-weight: 600;
        }

        .dropdown-item {
            margin-bottom: 18px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: 1px solid #e8e8e8;
        }

        .dropdown-item:hover {
            box-shadow: 0 4px 12px var(--shadow);
        }

        .dropdown-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--light-green-bg);
            padding: 18px 22px;
            cursor: pointer;
            border-left: 4px solid var(--accent-green);
            transition: all 0.3s ease;
        }

        .dropdown-header:hover {
            background: #d4ebe1;
            border-left-color: var(--primary-green);
        }

        .dropdown-header span {
            font-weight: 600;
            color: var(--primary-green-dark);
            font-size: 1.05rem;
            flex: 1;
            padding-right: 15px;
        }

        .dropdown-header i {
            color: var(--accent-green);
            font-size: 16px;
            transition: transform 0.3s ease, color 0.3s ease;
        }

        .dropdown-content {
            display: none;
            padding: 22px;
            background: white;
            font-size: 15px;
            line-height: 1.7;
            color: var(--gray-text);
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dropdown-item.active .dropdown-header {
            border-left-color: var(--primary-green);
            background: #d4ebe1;
        }

        .dropdown-item.active .dropdown-header i {
            transform: rotate(180deg);
            color: var(--primary-green);
        }

        .dropdown-item.active .dropdown-content {
            display: block;
        }

        .dropdown-content ol {
            margin: 15px 0;
            padding-left: 25px;
        }

        .dropdown-content li {
            margin: 8px 0;
            color: var(--gray-text);
        }

        .dropdown-content strong {
            color: var(--primary-green-dark);
        }

        .dropdown-content a {
            color: var(--accent-green);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .dropdown-content a:hover {
            color: var(--primary-green);
            text-decoration: underline;
        }

        /* Footer */
        .footer {
            background: linear-gradient(135deg, var(--primary-green-dark) 0%, var(--primary-green) 100%);
            color: white;
            padding: 50px 40px 30px;
            text-align: center;
            position: relative;
            margin-top: 60px;
        }

        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--accent-green), var(--primary-green-light), var(--accent-green));
        }

        .footer-content {
            max-width: 800px;
            margin: 0 auto;
        }

        .footer h2 {
            font-size: 2rem;
            margin-bottom: 20px;
            color: white;
            font-weight: 700;
        }

        .footer p {
            font-size: 1.05rem;
            line-height: 1.7;
            opacity: 0.95;
            margin-bottom: 25px;
        }

        .copyright {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            opacity: 0.8;
            font-size: 0.9rem;
        }

        /* Floating Chatbot Button */
        .chatbot-float {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 15px;
            cursor: pointer;
        }

        .chatbot-button {
            width: 65px;
            height: 65px;
            background: linear-gradient(135deg, var(--accent-green) 0%, var(--primary-green-light) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 6px 20px var(--shadow-hover);
            transition: all 0.3s ease;
            animation: pulse 2s infinite;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }

        .chatbot-button:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 25px var(--shadow-hover);
        }

        .chatbot-button i {
            font-size: 26px;
            color: white;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 6px 20px var(--shadow-hover);
            }

            50% {
                box-shadow: 0 6px 20px var(--shadow-hover), 0 0 0 0 rgba(58, 155, 111, 0.4);
            }

            100% {
                box-shadow: 0 6px 20px var(--shadow-hover), 0 0 0 15px rgba(58, 155, 111, 0);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-header {
                flex-direction: column;
                padding: 15px 20px;
                gap: 15px;
            }

            .logo-container h1 {
                font-size: 1.5rem;
                text-align: center;
            }

            nav ul {
                gap: 8px;
                flex-wrap: wrap;
                justify-content: center;
            }

            nav ul li a {
                padding: 8px 12px;
            }

            nav ul li a i {
                font-size: 18px;
            }

            nav ul li a span {
                font-size: 11px;
            }

            .page-header {
                padding: 40px 20px;
            }

            .page-header h1 {
                font-size: 2.2rem;
            }

            main {
                padding: 35px 20px;
            }

            .content-column {
                padding: 25px 20px;
            }

            .back-button {
                margin: 20px 20px 0;
            }
        }

        @media (max-width: 480px) {
            .logo-container {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }

            .logo-img {
                margin-right: 0;
            }

            .logo-container h1 {
                font-size: 1.3rem;
            }

            .page-header h1 {
                font-size: 1.8rem;
            }

            .dropdown-header {
                padding: 15px 18px;
            }

            .dropdown-header span {
                font-size: 0.95rem;
            }

            .dropdown-content {
                padding: 18px;
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header class="main-header">
        <div class="logo-container">
            <div class="logo-img">
                <img src="/MHO/media/chologo.png" alt="CHO Logo">
            </div>
            <h1>City Health Office of San Pablo</h1>
        </div>
        <nav>
            <?php echo generate_navigation(); ?>
        </nav>
    </header>

    <!-- Page Header -->
    <section class="page-header">
        <h1><i class="fas fa-file-alt"></i> Citizen's Charter</h1>
        <p>Complete list of health services available at the City Health Office of San Pablo</p>
    </section>

    <!-- Back Button -->
    <a href="index.php" class="back-button">
        <i class="fas fa-arrow-left"></i>Back to Home
    </a>

    <!-- Main Content -->
    <main>
        <!-- Search and Filter Section -->
        <div class="search-filter-section">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search for a service (e.g., 'medical certificate', 'burial', 'COVID')...">
                <i class="fas fa-search"></i>
            </div>

            <div class="filter-buttons">
                <span class="filter-label"><i class="fas fa-filter"></i> Quick Filters:</span>
                <button class="filter-btn active" data-category="all">All Services</button>
                <button class="filter-btn" data-category="medical">Medical Certificates</button>
                <button class="filter-btn" data-category="covid">COVID-19</button>
                <button class="filter-btn" data-category="death">Death & Burial</button>
                <button class="filter-btn" data-category="certification">Health Certifications</button>
            </div>
        </div>

        <!-- Quick Access Categories -->
        <div class="quick-access" style="display: none;">
            <div class="category-card" data-scroll="medical-employment">
                <i class="fas fa-briefcase"></i>
                <h4>Employment</h4>
                <p>Medical certificates for work</p>
            </div>
            <div class="category-card" data-scroll="covid-vaccination">
                <i class="fas fa-syringe"></i>
                <h4>Vaccination</h4>
                <p>COVID-19 certificates</p>
            </div>
            <div class="category-card" data-scroll="death-certificate">
                <i class="fas fa-certificate"></i>
                <h4>Death Records</h4>
                <p>Death certificates & permits</p>
            </div>
            <div class="category-card" data-scroll="health-workers">
                <i class="fas fa-user-md"></i>
                <h4>Workers</h4>
                <p>Health certifications</p>
            </div>
        </div>

        <!-- Results Info -->
        <div class="results-info" id="resultsInfo">
            <span class="results-count">Showing <strong id="resultsCount">25</strong> of 25 services</span>
            <button class="clear-search" id="clearSearch" style="display: none;">Clear Search</button>
        </div>

        <div class="content-column" id="servicesContainer">
            <div class="column-header">
                <div class="column-icon">
                    <i class="fas fa-list-check"></i>
                </div>
                <h2>All Health Services (25 Services)</h2>
            </div>

            <!-- 1. Sanitary Permit -->
            <div class="dropdown-item" data-category="certification" data-keywords="sanitary permit food establishment business restaurant">
                <div class="dropdown-header">
                    <span>Issuance of Sanitary Permit for Food and Non-Food Establishments</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="dropdown-content">
                    <strong>Pursuant to:</strong> The Code on Sanitation of the Philippines (P.D.856 Chapter III, Section 14a) and Revised Revenue Code of the City of San Pablo (Local Ordinance No. 196, s. 2024)<br>
                    <strong>Office or Division:</strong> City Health Office<br>
                    <strong>Who May Avail:</strong> Owner, Manager, or Operator<br>
                    <strong>Requirements:</strong>
                    <ol>
                        <li>A duly accomplished Unified Clearance Form</li>
                        <li>Barangay Business Permit</li>
                        <li>For those applying for renewal of Sanitary Permit previously issued Mayor's Permit</li>
                        <li>Additional Requirements (Click "More Details" For Clarification)</li>
                    </ol>
                    <a href="#">More Details</a>
                </div>
            </div>

            <!-- 2. Health Certification for Workers -->
            <div class="dropdown-item" data-category="certification" data-keywords="health certification workers business establishment employee" id="health-workers">
                <div class="dropdown-header">
                    <span>Issuance of Health Certification for Workers of Business Establishments</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="dropdown-content">
                    <strong>Pursuant to:</strong> The Code on Sanitation of the Philippines (P.D. 856 Chapter III, Section 15) and Revised Revenue Code of the City of San Pablo (Local Ordinance No. 196 s. 2024)<br>
                    <strong>Office or Division:</strong> City Health Office<br>
                    <strong>Who May Avail:</strong> Workers of food and non-food establishments<br>
                    <strong>Requirements:</strong>
                    <ol>
                        <li>Valid Laboratory Exam Result (Click "More Details" For Clarification)</li>
                        <li>1x1 ID Picture</li>
                        <li>Community Tax Certificate for the current Year</li>
                        <li>Identification Card</li>
                    </ol>
                    <a href="#">More Details</a>
                </div>
            </div>

            <!-- 3. Medical Certificates for Employment -->
            <div class="dropdown-item" data-category="medical" data-keywords="medical certificate employment job training loan scholarship school" id="medical-employment">
                <div class="dropdown-header">
                    <span>Issuance of Medical Certificates for Employment, On-the-job-training, Loans, Scholarships, School Entrants</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="dropdown-content">
                    As required by employers, schools and financial institutions. Fees collected is pursuant to Revised Revenue Code of the City of San Pablo (Local Ordinance No. 196 s. 2024)<br>
                    <strong>Office or Division:</strong> City Health Office<br>
                    <strong>Who May Avail:</strong> Applicants for employment, on-the-job-training, loans, scholarships, school entrant<br>
                    <strong>Requirements:</strong>
                    <ol>
                        <li>Identification Card</li>
                        <li>Valid Laboratory Exam Results (Click "More Details" For Clarification)</li>
                    </ol>
                    <a href="#">More Details</a>
                </div>
            </div>

            <!-- 4. Tricycle Driver Medical Certificate -->
            <div class="dropdown-item" data-category="medical" data-keywords="tricycle driver medical certificate franchise license">
                <div class="dropdown-header">
                    <span>Issuance of Medical Certificate for Tricycle Drivers (Tricycle Franchise)</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="dropdown-content">
                    <strong>Pursuant to:</strong> Local Ordinance No. 2011-01 (The 2011 Revised Comprehensive Traffic Code of the City of San Pablo, and Creating a Comprehensive and Integrated Traffic Management System/Traffic Assessment Plan in the City of San Pablo)<br>
                    <strong>Office or Division:</strong> City Health Office<br>
                    <strong>Who May Avail:</strong> Tricycle Drivers<br>
                    <strong>Requirements:</strong>
                    <ol>
                        <li>Driver's License</li>
                        <li>Unified Clearance</li>
                    </ol>
                    <a href="#">More Details</a>
                </div>
            </div>

            <!-- 5. Medical Certificate for Leave -->
            <div class="dropdown-item" data-category="medical" data-keywords="medical certificate leave absence sick leave government employee">
                <div class="dropdown-header">
                    <span>Issuance of Medical Certificate for Leave of Absence</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="dropdown-content">
                    As required by private employers and pursuant to CSC MC No. 41, s. 1998<br>
                    <strong>Office or Division:</strong> City Health Office<br>
                    <strong>Who May Avail:</strong> Government Employees and General Public<br>
                    <strong>Requirements:</strong>
                    <ol>
                        <li>Consultation within the first three (3) days of illness</li>
                        <li>Laboratory Test (If Available)</li>
                    </ol>
                    <a href="#">More Details</a>
                </div>
            </div>

            <!-- 6. PWD Medical Certificates -->
            <div class="dropdown-item" data-category="medical" data-keywords="pwd persons with disabilities medical certificate disability">
                <div class="dropdown-header">
                    <span>Issuance of Medical Certificates for Persons with Disabilities (PWDs)</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="dropdown-content">
                    <strong>Pursuant to:</strong> National Council on Disability Affairs Administrative Order No. 001, s. 2008<br>
                    <strong>Office or Division:</strong> City Health Office<br>
                    <strong>Who May Avail:</strong> Persons with Disabilities and/or their relatives<br>
                    <strong>Requirements:</strong>
                    <ol>
                        <li>Philippine Registry Form for Persons with Disabilities</li>
                        <li>Certification from a Specialist if the disability is uncertain (e.g. Psychiatrist for Psychosocial Disability)</li>
                        <li>Proof of the disability if client is unable to report for physical examination and assessment</li>
                    </ol>
                    <a href="#">More Details</a>
                </div>
            </div>

            <!-- 7. Entertainers Health Certification -->
            <div class="dropdown-item" data-category="certification" data-keywords="entertainers health certification entertainment establishment">
                <div class="dropdown-header">
                    <span>Issuance of Health Certification for Entertainers of Entertainment Establishments</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="dropdown-content">
                    <strong>Pursuant to:</strong> The Code on Sanitation of the Philippines (P.D. 856 Chapter XI, Section 57.b.1.) and Local Ordinance No. 2006-35 (Codified as of March 30, 2011), Section 7.e<br>
                    <strong>Office or Division:</strong> City Health Office<br>
                    <strong>Who May Avail:</strong> Entertainers of Entertainment Establishments<br>
                    <strong>Requirements:</strong>
                    <ol>
                        <li>Barangay Clearance</li>
                        <li>Community Tax Certificate for the Current Year</li>
                        <li>Valid laboratory exam results: (within 2 weeks) urinalysis, fecalysis, sputum exam and (within a year) chest x-ray</li>
                        <li>Two pieces 1 x 1 and two pieces 2 x 2 ID pictures</li>
                        <li>Dental Clearance (for new applicants only)</li>
                        <li>Authenticated Birth Certificate (for applicants whose age-range cannot be determined)</li>
                    </ol>
                    <a href="#">More Details</a>
                </div>
            </div>

            <!-- 8. BJMP Detention Medical Certificate -->
            <div class="dropdown-item" data-category="medical" data-keywords="bjmp detention medical certificate police custody suspect arrested">
                <div class="dropdown-header">
                    <span>Issuance of Medical Certificates for BJMP Detention</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="dropdown-content">
                    <strong>Pursuant to:</strong> PNP Operational Procedures, March 2010, Section 10 (Medical Examination of Arrested Person/Suspect)<br>
                    <strong>Office or Division:</strong> City Health Office<br>
                    <strong>Who May Avail:</strong> Suspects under PNP Custody<br>
                    <strong>Requirements:</strong>
                    <ol>
                        <li>PNP request for Physical Examination</li>
                    </ol>
                    <a href="#">More Details</a>
                </div>
            </div>

            <!-- 9. Drug Counseling -->
            <div class="dropdown-item" data-category="certification" data-keywords="drug counseling screening dependent probationary rehabilitation">
                <div class="dropdown-header">
                    <span>Online Counseling and Screening of Drug Dependents on Probationary Status</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="dropdown-content">
                    As requested by Regional or Municipal Trial Courts and pursuant to DDB Board Regulation No. 2, Series of 2006<br>
                    <strong>Office or Division:</strong> City Health Office<br>
                    <strong>Who May Avail:</strong> Drug Dependent Individuals on Probationary Status<br>
                    <strong>Requirements:</strong>
                    <ol>
                        <li>Court Order</li>
                        <li>Confirmed schedule for online counseling and screening</li>
                        <li>Latest drug test result</li>
                    </ol>
                    <a href="#">More Details</a>
                </div>
            </div>

            <!-- 10. Drug Referral -->
            <div class="dropdown-item" data-category="certification" data-keywords="drug referral rehabilitation cbdrp facility dependent">
                <div class="dropdown-header">
                    <span>Referral of Drug Dependents for Community Based Rehabilitation Program (CBDRP) or Drug Rehabilitation Facility</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="dropdown-content">
                    <strong>Pursuant to:</strong> DDB Board Regulation No. 2, Series of 2006<br>
                    <strong>Office or Division:</strong> City Health Office<br>
                    <strong>Who May Avail:</strong> Drug Dependent Individuals for referral to CBDRP or Drug Rehabilitation Facility<br>
                    <strong>Requirements:</strong>
                    <ol>
                        <li>Referral letter from Barangay Chairman or City Social Welfare and Development Officer (CSWDO) or Private Employer</li>
                        <li>Latest drug test result</li>
                    </ol>
                    <a href="#">More Details</a>
                </div>
            </div>

            <!-- 11. Gender Certification -->
            <div class="dropdown-item" data-category="certification" data-keywords="gender certification physical examination civil registrar">
                <div class="dropdown-header">
                    <span>Certification and Physical Examination for Gender</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="dropdown-content">
                    <strong>Pursuant to:</strong> Philippine Statistics Authority Administrative Order No. 1, Series of 2012, Rules and Regulations Governing the Implementation of Republic Act. No. 10172<br>
                    <strong>Office or Division:</strong> City Health Office<br>
                    <strong>Who May Avail:</strong> General Public<br>
                    <strong>Requirements:</strong>
                    <ol>
                        <li>Minors need to be accompanied by parent or guardian</li>
                        <li>Requirement slip from Local Civil Registrar's Office</li>
                        <li>Identification Card</li>
                        <li>Letter of Consent</li>
                    </ol>
                    <a href="#">More Details</a>
                </div>
            </div>

            <!-- 12. Physical Injuries Certification -->
            <div class="dropdown-item" data-category="certification" data-keywords="physical injuries certification examination vawc violence abuse">
                <div class="dropdown-header">
                    <span>Certification and Physical Examination for Physical Injuries</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="dropdown-content">
                    As requested by Philippine National Police or Office of City Social Welfare and Development Officer and pursuant to Revised PNP Operational Procedures 2013 (Rule 33. Investigation of Violence Against Women and their Children (VAWC) and other Cases of Child Abuse)<br>
                    <strong>Office or Division:</strong> City Health Office<br>
                    <strong>Who May Avail:</strong> General Public<br>
                    <strong>Requirements:</strong>
                    <ol>
                        <li>OSWD or PNP request for physical examination. Minors need to be accompanied by parent/guardian</li>
                    </ol>
                    <a href="#">More Details</a>
                </div>
            </div>

            <!-- 13. Sexual Abuse Certification -->
            <div class="dropdown-item" data-category="certification" data-keywords="sexual abuse certification examination victim vawc violence">
                <div class="dropdown-header">
                    <span>Certification and Physical Examination for Sexual Abuse</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="dropdown-content">
                    As requested by Philippine National Police or Office of City Social Welfare and Development Officer and pursuant to Revised PNP Operational Procedures 2013 (Rule 33. Investigation of Violence Against Women and their Children (VAWC) and other Cases of Child Abuse)<br>
                    <strong>Office or Division:</strong> City Health Office<br>
                    <strong>Who May Avail:</strong> Alleged Sexual Abuse Victim<br>
                    <strong>Requirements:</strong>
                    <ol>
                        <li>OSWD or PNP request for physical examination. Minors need to be accompanied by parent/guardian</li>
                    </ol>
                    <a href="#">More Details</a>
                </div>
            </div>

            <!-- 14. Burial Authorization (Indigent) -->
            <div class="dropdown-item" data-category="death" data-keywords="burial authorization permit indigent cemetery himlayang san pablena">
                <div class="dropdown-header">
                    <span>Issuance of Burial Authorization for Burial Permit (Indigent) In Himlayang San Pableña</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="dropdown-content">
                    <strong>Pursuant to:</strong> Revised Revenue Code of the City of San Pablo (Local Ordinance No. 196 s. 2024)<br>
                    <strong>Office or Division:</strong> City Health Office<br>
                    <strong>Who May Avail:</strong> General Public<br>
                    <strong>Requirements:</strong>
                    <ol>
                        <li>Applicant must have knowledge of personal data of deceased and information on internment such as funeral service, date and time</li>
                        <li>Written request or Certification of Indigency for exemption from payment of digging fee due to indigency issued by the Barangay Chairman (residence of the deceased)</li>
                    </ol>
                    <a href="#">More Details</a>
                </div>
            </div>

            <!-- 15. Death Certificate (No Medical Attendance) -->
            <div class="dropdown-item" data-category="death" data-keywords="death certificate without medical attendance died no doctor" id="death-certificate">
                <div class="dropdown-header">
                    <span>Issuance of Death Certificate for Deaths without Medical Attendance</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="dropdown-content">
                    <strong>Pursuant to:</strong> The Code on Sanitation of the Philippines (P.D. 856, Chapter XXI. Disposal of Dead Persons, Section 4. Burial Requirements) and Medical Certification of Death, DOH Death Registration: Legal Mandates, Rules and Procedures<br>
                    <strong>Office or Division:</strong> City Health Office<br>
                    <strong>Who May Avail:</strong> General Public<br>
                    <strong>Requirements:</strong>
                    <ol>
                        <li>Applicant, preferably a next-of-kin or close relative, must have knowledge of the personal data of the deceased as well as the circumstances leading to the death</li>
                        <li>Certification of Licensed Embalmer at the back of Death Certificate Form</li>
                    </ol>
                    <a href="#">More Details</a>
                </div>
            </div>

            <!-- 16. Death Certificate (With Medical Attendance) -->
            <div class="dropdown-item" data-category="death" data-keywords="death certificate with medical attendance died doctor physician">
                <div class="dropdown-header">
                    <span>Issuance of Death Certificate for Deaths with Medical Attendance</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="dropdown-content">
                    <strong>Pursuant to:</strong> The Code on Sanitation of the Philippines (P.D. 856, Chapter XXI. Disposal of Dead Persons, Section 4. Burial Requirements) and Medical Certification of Death, DOH Death Registration: Legal Mandates, Rules and Procedures<br>
                    <strong>Office or Division:</strong> City Health Office<br>
                    <strong>Who May Avail:</strong> General Public<br>
                    <strong>Requirements:</strong>
                    <ol>
                        <li>An accomplished Death Certificate form duly signed by the attending physician</li>
                        <li>Certification of Licensed Embalmer at the back of Death Certificate Form</li>
                    </ol>
                    <a href="#">More Details</a>
                </div>
            </div>

            <!-- 17. Exhumation Permit -->
            <div class="dropdown-item" data-category="death" data-keywords="exhumation permit disinterment burial transfer remains">
                <div class="dropdown-header">
                    <span>Issuance of Exhumation Permit</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="dropdown-content">
                    <strong>Pursuant to:</strong> The Code on Sanitation of the Philippines (P.D. 856, Chapter XXI. Disposal of Dead Persons, Section 5. Disinterment or Exhumation Requirements)<br>
                    <strong>Office or Division:</strong> City Health Office<br>
                    <strong>Who May Avail:</strong> General Public<br>
                    <strong>Requirements:</strong>
                    <ol>
                        <li>Burial Sketch if buried at City Cemetery</li>
                        <li>Copy of Death Certificate Note: Period of burial should not be less than 3 years for non-communicable diseases cause of death, and not less than 5 years for communicable diseases cause of death</li>
                    </ol>
                    <a href="#">More Details</a>
                </div>
            </div>

            <!-- 18. Postmortem Examination -->
            <div class="dropdown-item" data-category="death" data-keywords="postmortem medicolegal examination autopsy dissection remains">
                <div class="dropdown-header">
                    <span>Postmortem Medicolegal Examination</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="dropdown-content">
                    <strong>Pursuant to:</strong> The Code on Sanitation of the Philippines (P.D. 856, Chapter XXI. Disposal of Dead Persons, Section 13. Autopsy and Dissection of Remains) and Revised Revenue Code of the City of San Pablo (Local Ordinance No. 196 s. 2024)<br>
                    <strong>Office or Division:</strong> City Health Office<br>
                    <strong>Who May Avail:</strong> General Public<br>
                    <strong>Requirements:</strong>
                    <ol>
                        <li>PNP request for postmortem examination</li>
                        <li>Informant who have knowledge of the personal data of the deceased and of the alleged circumstances of death</li>
                        <li>Death Certificate Form</li>
                    </ol>
                    <a href="#">More Details</a>
                </div>
            </div>

            <!-- 19. Research and Information -->
            <div class="dropdown-item" data-category="certification" data-keywords="research information securing request data records">
                <div class="dropdown-header">
                    <span>Researches and Securing Information</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="dropdown-content">
                    <strong>Pursuant to:</strong> Executive Order No. 02, s. 2016 (Operationalizing in the Executive Branch the People's Constitutional Right to Information and the State Policies to Full Public Disclosure and Transparency in the Public Service and Providing Guidelines Therefore)<br>
                    <strong>Office or Division:</strong> City Health Office<br>
                    <strong>Who May Avail:</strong> General Public<br>
                    <strong>Requirements:</strong>
                    <ol>
                        <li>Written Letter of Request</li>
                        <li>Identification Card</li>
                    </ol>
                    <a href="#">More Details</a>
                </div>
            </div>

            <!-- 20. Complaint Management -->
            <div class="dropdown-item" data-category="certification" data-keywords="complaint management anti red tape feedback grievance">
                <div class="dropdown-header">
                    <span>Complaint Management</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="dropdown-content">
                    <strong>Pursuant to:</strong> Republic Act No. 9485 (Anti Red Tape Act of 2007)<br>
                    <strong>Office or Division:</strong> City Health Office<br>
                    <strong>Who May Avail:</strong> General Public<br>
                    <strong>Requirements:</strong>
                    <ol>
                        <li>Written letter of complaint with supporting documents if any</li>
                    </ol>
                    <a href="#">More Details</a>
                </div>
            </div>

            <!-- 21. COVID-19 Vaccination Certificate (National) -->
            <div class="dropdown-item" data-category="covid" data-keywords="covid vaccination certificate national digital vaccine vaxcert" id="covid-vaccination">
                <div class="dropdown-header">
                    <span>Issuance of COVID-19 Vaccination Certificate (National Digital Vaccination Certificate)</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="dropdown-content">
                    <strong>Pursuant to:</strong> DILG Department Memorandum No. 2021-095 based on R.A. 11525 (An Act Establishing the Coronavirus Disease 2019 (COVID-19) Vaccination Program Expediting the Vaccine Procurement and Administration Process, Providing Funds therefor, and for other purposes)<br>
                    <strong>Office or Division:</strong> City Health Office-Health Information Center, Trece Martirez St., San Pablo City<br>
                    <strong>Who May Avail:</strong> General Public<br>
                    <strong>Requirements:</strong>
                    <ol>
                        <li>Vaccination Card</li>
                        <li>Identification Card</li>
                    </ol>
                    <a href="#">More Details</a>
                </div>
            </div>

            <!-- 22. COVID-19 Vaccination Certificate (LGU) -->
            <div class="dropdown-item" data-category="covid" data-keywords="covid vaccination certificate lgu local vaccine">
                <div class="dropdown-header">
                    <span>Issuance of LGU COVID-19 Vaccination Certificate</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="dropdown-content">
                    <strong>Pursuant to:</strong> DILG Department Memorandum No. 2021-095 based on R.A. 11525 (An Act Establishing the Coronavirus Disease 2019 (COVID-19) Vaccination Program Expediting the Vaccine Procurement and Administration Process, Providing Funds therefor, and for other purposes)<br>
                    <strong>Office or Division:</strong> City Health Office-Health Information Center, Trece Martirez St., San Pablo City<br>
                    <strong>Who May Avail:</strong> General Public<br>
                    <strong>Requirements:</strong>
                    <ol>
                        <li>Vaccination Card</li>
                        <li>Identification Card</li>
                    </ol>
                    <a href="#">More Details</a>
                </div>
            </div>

            <!-- 23. Immunization Certificate -->
            <div class="dropdown-item" data-category="medical" data-keywords="immunization certificate vaccine children infants mandatory">
                <div class="dropdown-header">
                    <span>Issuance of Immunization Certificate</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="dropdown-content">
                    <strong>Pursuant to:</strong> Republic Act. No. 10152 (Mandatory Infants and Children Health Immunization Act of 2011) and Revised Revenue Code of the City of San Pablo (Local Ordinance No. 196 s. 2024)<br>
                    <strong>Office or Division:</strong> City Health Office<br>
                    <strong>Who May Avail:</strong> General Public<br>
                    <strong>Requirements:</strong>
                    <ol>
                        <li>Immunization Record; or Individual Treatment Record (ITR)</li>
                        <li>Identification Card</li>
                        <li>Medical Records Release Request Form</li>
                    </ol>
                    <a href="#">More Details</a>
                </div>
            </div>

            <!-- 24. Lost Vaccination Card Replacement -->
            <div class="dropdown-item" data-category="covid" data-keywords="lost vaccination card replacement covid vaccine missing">
                <div class="dropdown-header">
                    <span>Replacement of Lost COVID-19 Vaccination Card</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="dropdown-content">
                    <strong>Pursuant to:</strong> DILG Department Memorandum No. 2021-095 based on R.A. 11525 (An Act Establishing the Coronavirus Disease 2019 (COVID-19) Vaccination Program Expediting the Vaccine Procurement and Administration Process, Providing Funds therefor, and for other purposes)<br>
                    <strong>Office or Division:</strong> City Health Office-Health Information Center, Trece Martirez St., San Pablo City<br>
                    <strong>Who May Avail:</strong> General Public<br>
                    <strong>Requirements:</strong>
                    <ol>
                        <li>Affidavit of Loss</li>
                        <li>Photocopy of Identification Card</li>
                    </ol>
                    <a href="#">More Details</a>
                </div>
            </div>

            <!-- 25. COVID Medical Clearance -->
            <div class="dropdown-item" data-category="covid" data-keywords="covid medical clearance positive recovered certificate">
                <div class="dropdown-header">
                    <span>Issuance of Medical Clearance Certificate for COVID-19 Positive Clients</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="dropdown-content">
                    <strong>Pursuant to:</strong> DILG Department Memorandum No. 2021-095 based on R.A. 11525 (An Act Establishing the Coronavirus Disease 2019 (COVID-19) Vaccination Program Expediting the Vaccine Procurement and Administration Process, Providing Funds Therefor, and for other purposes)<br>
                    <strong>Office or Division:</strong> City Health Office<br>
                    <strong>Who May Avail:</strong> General Public<br>
                    <strong>Requirements:</strong>
                    <ol>
                        <li>Identification Card</li>
                    </ol>
                    <a href="#">More Details</a>
                </div>
            </div>

        </div>

        <!-- No Results Message (Hidden by default) -->
        <div class="no-results" id="noResults" style="display: none;">
            <i class="fas fa-search-minus"></i>
            <h3>No Services Found</h3>
            <p>Try adjusting your search or filter to find what you're looking for.</p>
        </div>
    </main>

    <!-- Scroll to Top Button -->
    <div class="scroll-top" id="scrollTop">
        <i class="fas fa-arrow-up"></i>
    </div>

    <!-- Floating Chatbot Button -->
    <div class="chatbot-float" onclick="window.location.href='chatbot.php'">
        <div class="chatbot-button">
            <i class="fas fa-robot"></i>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <h2>About Us</h2>
            <p>The City Health Office of San Pablo is dedicated to providing quality healthcare services to the residents of San Pablo City. Our mission is to promote health, prevent disease, and protect the well-being of our community through accessible and responsive healthcare programs.</p>

            <div class="copyright">
                <p>&copy; 2024 City Health Office of San Pablo. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // JavaScript for dropdown functionality
        document.addEventListener('DOMContentLoaded', function() {
            const dropdownItems = document.querySelectorAll('.dropdown-item');

            dropdownItems.forEach(item => {
                const header = item.querySelector('.dropdown-header');

                header.addEventListener('click', function() {
                    // Close all other dropdowns
                    dropdownItems.forEach(otherItem => {
                        if (otherItem !== item && otherItem.classList.contains('active')) {
                            otherItem.classList.remove('active');
                        }
                    });

                    // Toggle current dropdown
                    item.classList.toggle('active');
                });
            });
        });
    </script>
</body>

</html>