<?php 
// The layout is already set in the View class constructor
?>

<div class="container-fluid p-0">
    <!-- Hero Section with Background -->
    <div class="hero-section text-white text-center py-5" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); min-height: 60vh; display: flex; align-items: center;">
        <div class="container">
            <h1 class="display-3 mb-4 animate__animated animate__fadeIn">Welcome to LMS Recovery Analytics</h1>
            <p class="lead mb-4 animate__animated animate__fadeIn animate__delay-1s">Your Smart Solution for Loan Management and Recovery</p>
            <div class="mt-5 animate__animated animate__fadeIn animate__delay-2s">
                <?php if (!isset($user)): ?>
                    <a href="/auth/login" class="btn btn-light btn-lg px-4 me-3 shadow-sm">Get Started <i class="fas fa-arrow-right ms-2"></i></a>
                    <a href="/about" class="btn btn-outline-light btn-lg px-4">Learn More</a>
                <?php else: ?>
                    <a href="/dashboard" class="btn btn-light btn-lg px-4 shadow-sm">Go to Dashboard <i class="fas fa-tachometer-alt ms-2"></i></a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="container mt-5">
        <div class="row g-4">
            <!-- Feature 1 -->
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm hover-card">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-chart-line fa-3x text-primary"></i>
                        </div>
                        <h3 class="card-title h4 mb-3">Real-time Analytics</h3>
                        <p class="card-text text-muted">Track loan recovery performance with advanced analytics and instant insights. Make data-driven decisions effortlessly.</p>
                    </div>
                </div>
            </div>

            <!-- Feature 2 -->
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm hover-card">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-robot fa-3x text-primary"></i>
                        </div>
                        <h3 class="card-title h4 mb-3">Smart Recovery</h3>
                        <p class="card-text text-muted">Leverage AI-powered recommendations to optimize your recovery strategies and improve collection rates.</p>
                    </div>
                </div>
            </div>

            <!-- Feature 3 -->
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm hover-card">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-file-alt fa-3x text-primary"></i>
                        </div>
                        <h3 class="card-title h4 mb-3">Comprehensive Reports</h3>
                        <p class="card-text text-muted">Generate detailed reports with beautiful visualizations to gain deeper insights into your recovery process.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="container mt-5 py-5">
        <div class="row text-center">
            <div class="col-md-4 mb-4">
                <h2 class="display-4 fw-bold text-primary">98%</h2>
                <p class="text-muted">Recovery Rate</p>
            </div>
            <div class="col-md-4 mb-4">
                <h2 class="display-4 fw-bold text-primary">50K+</h2>
                <p class="text-muted">Cases Handled</p>
            </div>
            <div class="col-md-4 mb-4">
                <h2 class="display-4 fw-bold text-primary">24/7</h2>
                <p class="text-muted">Support Available</p>
            </div>
        </div>
    </div>
</div>

<!-- Add custom styles -->
<style>
.hero-section {
    position: relative;
    overflow: hidden;
}

.hover-card {
    transition: transform 0.3s ease-in-out;
}

.hover-card:hover {
    transform: translateY(-10px);
}

.feature-icon {
    height: 80px;
    width: 80px;
    margin: 0 auto;
    background: rgba(30, 60, 114, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.animate__animated {
    animation-duration: 1s;
}

.animate__delay-1s {
    animation-delay: 0.5s;
}

.animate__delay-2s {
    animation-delay: 1s;
}
</style>
