<template>
  <div class="min-h-screen bg-bg">
    <!-- Navigation Header -->
    <header class="border-b border-accent/20 bg-white sticky top-0 z-40">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center">
            <span class="text-white font-bold text-lg">CC</span>
          </div>
          <h1 class="text-xl font-bold text-primary">CivicConnect</h1>
        </div>
        <nav class="hidden md:flex gap-8">
          <a href="#features" class="text-text hover:text-primary transition">Features</a>
          <a href="#how-it-works" class="text-text hover:text-primary transition">How it Works</a>
          <a href="#community" class="text-text hover:text-primary transition">Community</a>
        </nav>
        <div class="flex gap-3">
          <router-link
            to="/login"
            class="px-6 py-2 text-primary border border-primary rounded-lg hover:bg-primary/5 transition"
          >
            Sign In
          </router-link>
          <router-link
            to="/register"
            class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition"
          >
            Get Started
          </router-link>
        </div>
      </div>
    </header>

    <!-- Hero Section -->
    <section class="py-12 md:py-24 px-4 sm:px-6 lg:px-8">
      <div class="max-w-4xl mx-auto text-center">
        <h2 class="text-4xl md:text-5xl lg:text-6xl font-bold text-primary mb-6">
          Make Your Community Better
        </h2>
        <p class="text-lg md:text-xl text-muted mb-8 max-w-2xl mx-auto">
          Report city issues, engage with your community, and watch solutions come to life. Every voice matters in building a better tomorrow.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <router-link
            to="/register"
            class="px-8 py-3 bg-primary text-white rounded-lg font-semibold hover:bg-primary/90 transition inline-block"
          >
            Start Reporting
          </router-link>
          <router-link
            to="/login"
            class="px-8 py-3 bg-white text-primary border-2 border-primary rounded-lg font-semibold hover:bg-primary/5 transition inline-block"
          >
            Sign In
          </router-link>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-12 md:py-20 px-4 sm:px-6 lg:px-8 bg-white border-t border-accent/20">
      <div class="max-w-6xl mx-auto">
        <div class="text-center mb-12">
          <h2 class="text-3xl md:text-4xl font-bold text-primary mb-4">Why Choose CivicConnect?</h2>
          <p class="text-lg text-muted">Empowering citizens and connecting communities</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <!-- Feature 1 -->
          <div class="p-6 rounded-xl border border-accent/20 hover:border-accent/40 hover:shadow-md transition">
            <div class="w-12 h-12 bg-accent/10 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-primary mb-2">Report with Location</h3>
            <p class="text-muted">Pin issues on a map with photos and descriptions. Help officials understand exactly where problems are.</p>
          </div>

          <!-- Feature 2 -->
          <div class="p-6 rounded-xl border border-accent/20 hover:border-accent/40 hover:shadow-md transition">
            <div class="w-12 h-12 bg-accent/10 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2 1m2-1l-2-1m2 1v2.5" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-primary mb-2">Community Engagement</h3>
            <p class="text-muted">Upvote issues you care about, comment with solutions, and build consensus with your neighbors.</p>
          </div>

          <!-- Feature 3 -->
          <div class="p-6 rounded-xl border border-accent/20 hover:border-accent/40 hover:shadow-md transition">
            <div class="w-12 h-12 bg-accent/10 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-primary mb-2">Track Progress</h3>
            <p class="text-muted">Stay updated with real-time status changes. See when issues are being addressed by city staff.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-12 md:py-20 px-4 sm:px-6 lg:px-8 bg-bg">
      <div class="max-w-4xl mx-auto">
        <div class="text-center mb-12">
          <h2 class="text-3xl md:text-4xl font-bold text-primary mb-4">How It Works</h2>
        </div>
        
        <div class="space-y-8">
          <div class="flex gap-6">
            <div class="shrink-0">
              <div class="flex items-center justify-center h-10 w-10 rounded-full bg-primary text-white font-bold">
                1
              </div>
            </div>
            <div>
              <h3 class="text-xl font-bold text-primary mb-2">Create Your Account</h3>
              <p class="text-muted">Sign up with your email and verify it. Join thousands of engaged citizens.</p>
            </div>
          </div>

          <div class="flex gap-6">
            <div class="shrink-0">
              <div class="flex items-center justify-center h-10 w-10 rounded-full bg-primary text-white font-bold">
                2
              </div>
            </div>
            <div>
              <h3 class="text-xl font-bold text-primary mb-2">Report or Explore Issues</h3>
              <p class="text-muted">Spot a pothole? Broken light? Submit a report with photos and location. Or browse existing issues.</p>
            </div>
          </div>

          <div class="flex gap-6">
            <div class="shrink-0">
              <div class="flex items-center justify-center h-10 w-10 rounded-full bg-primary text-white font-bold">
                3
              </div>
            </div>
            <div>
              <h3 class="text-xl font-bold text-primary mb-2">Engage & Support</h3>
              <p class="text-muted">Upvote issues you care about. Comment with solutions and discuss with other citizens.</p>
            </div>
          </div>

          <div class="flex gap-6">
            <div class="shrink-0">
              <div class="flex items-center justify-center h-10 w-10 rounded-full bg-primary text-white font-bold">
                4
              </div>
            </div>
            <div>
              <h3 class="text-xl font-bold text-primary mb-2">Monitor Resolution</h3>
              <p class="text-muted">Follow progress as city staff addresses issues. Celebrate wins with your community.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Community Stats Section -->
    <section id="community" class="py-12 md:py-20 px-4 sm:px-6 lg:px-8 bg-white border-t border-accent/20">
      <div class="max-w-6xl mx-auto">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
          <div>
            <p class="text-3xl md:text-4xl font-bold text-primary mb-2">2,547</p>
            <p class="text-muted">Issues Reported</p>
          </div>
          <div>
            <p class="text-3xl md:text-4xl font-bold text-primary mb-2">12.3K</p>
            <p class="text-muted">Active Citizens</p>
          </div>
          <div>
            <p class="text-3xl md:text-4xl font-bold text-primary mb-2">89%</p>
            <p class="text-muted">Resolution Rate</p>
          </div>
          <div>
            <p class="text-3xl md:text-4xl font-bold text-primary mb-2">45 days</p>
            <p class="text-muted">Avg Resolution</p>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-12 md:py-20 px-4 sm:px-6 lg:px-8 bg-primary text-white">
      <div class="max-w-4xl mx-auto text-center">
        <h2 class="text-3xl md:text-4xl font-bold mb-6">Ready to Make a Difference?</h2>
        <p class="text-lg mb-8 opacity-90">Join our community today and help make cities better for everyone.</p>
        <router-link
          to="/register"
          class="px-8 py-3 bg-white text-primary rounded-lg font-semibold hover:bg-accent/20 transition inline-block"
        >
          Get Started Now
        </router-link>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white border-t border-accent/20 py-8 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto text-center text-muted text-sm">
        <p>&copy; 2025 CivicConnect. Empowering communities, one report at a time.</p>
      </div>
    </footer>
  </div>
</template>

<script setup>
</script>
