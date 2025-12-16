<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-12 px-4">
    <div class="max-w-7xl mx-auto">
      <!-- Hero Section -->
      <div class="text-center mb-12">
        <h1 class="text-5xl font-bold text-gray-900 mb-4">Welcome to CivicConnect</h1>
        <p class="text-xl text-gray-600 mb-8">Report city issues, upvote solutions, and make your community better</p>
        <div class="flex gap-4 justify-center">
          <router-link
            to="/register"
            class="bg-indigo-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-indigo-700 transition"
          >
            Get Started
          </router-link>
          <router-link
            to="/login"
            class="bg-white text-indigo-600 px-8 py-3 rounded-lg font-semibold border-2 border-indigo-600 hover:bg-indigo-50 transition"
          >
            Sign In
          </router-link>
        </div>
      </div>

      <!-- Features Section -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
        <div class="bg-white rounded-lg shadow-lg p-6">
          <div class="text-3xl mb-4">📍</div>
          <h3 class="text-xl font-bold mb-2">Report Issues</h3>
          <p class="text-gray-600">Report city problems with location and photos</p>
        </div>
        <div class="bg-white rounded-lg shadow-lg p-6">
          <div class="text-3xl mb-4">👍</div>
          <h3 class="text-xl font-bold mb-2">Upvote & Engage</h3>
          <p class="text-gray-600">Support issues and discuss solutions with others</p>
        </div>
        <div class="bg-white rounded-lg shadow-lg p-6">
          <div class="text-3xl mb-4">✅</div>
          <h3 class="text-xl font-bold mb-2">Track Progress</h3>
          <p class="text-gray-600">Monitor status updates from staff and officials</p>
        </div>
      </div>

      <!-- Recent Issues Preview -->
      <div class="bg-white rounded-lg shadow-lg p-8">
        <h2 class="text-2xl font-bold mb-6">Recent Issues</h2>
        <p class="text-gray-600">Sign in to view and interact with reported issues</p>
      </div>
    </div>
  </div>
</template>

<script setup>
</script>
