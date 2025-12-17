<template>
  <div class="min-h-screen bg-bg flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-md">
      <!-- Logo & Header -->
      <div class="text-center mb-8">
        <div class="flex items-center justify-center gap-2 mb-4">
          <div class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center">
            <span class="text-white font-bold text-lg">CC</span>
          </div>
          <h1 class="text-2xl font-bold text-primary">CivicConnect</h1>
        </div>
        <h2 class="text-2xl font-bold text-primary mb-2">Welcome Back</h2>
        <p class="text-muted">Sign in to your account to continue</p>
      </div>

      <!-- Login Form -->
      <form @submit.prevent="handleLogin" class="bg-white rounded-xl shadow-md p-8 space-y-6">
        <!-- Email Field -->
        <div>
          <label for="email" class="block text-sm font-medium text-primary mb-2">
            Email Address
          </label>
          <input
            id="email"
            v-model="formData.email"
            type="email"
            name="email"
            placeholder="you@example.com"
            class="w-full px-4 py-2 border border-accent/30 rounded-lg focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent transition"
            @blur="validateField('email')"
          />
          <p v-if="errors.email" class="mt-1 text-sm text-danger">{{ errors.email }}</p>
        </div>

        <!-- Password Field -->
        <div>
          <div class="flex justify-between items-center mb-2">
            <label for="password" class="block text-sm font-medium text-primary">
              Password
            </label>
            <router-link to="/forgot-password" class="text-sm text-accent hover:text-primary transition">
              Forgot password?
            </router-link>
          </div>
          <input
            id="password"
            v-model="formData.password"
            type="password"
            name="password"
            placeholder="••••••••"
            class="w-full px-4 py-2 border border-accent/30 rounded-lg focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent transition"
            @blur="validateField('password')"
          />
          <p v-if="errors.password" class="mt-1 text-sm text-danger">{{ errors.password }}</p>
        </div>

        <!-- Remember Me -->
        <div class="flex items-center">
          <input
            id="remember"
            v-model="formData.rememberMe"
            type="checkbox"
            name="remember"
            class="h-4 w-4 text-primary focus:ring-accent border-accent/30 rounded cursor-pointer"
          />
          <label for="remember" class="ml-2 block text-sm text-muted cursor-pointer">
            Remember me
          </label>
        </div>

        <!-- Error Message -->
        <div v-if="error" class="p-4 bg-danger/10 border border-danger/30 rounded-lg">
          <p class="text-sm text-danger">{{ error }}</p>
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          :disabled="isLoading"
          class="w-full py-2 px-4 bg-primary text-white rounded-lg font-semibold hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed transition"
        >
          <span v-if="!isLoading">Sign In</span>
          <span v-else>Signing in...</span>
        </button>

        <!-- Sign Up Link -->
        <div class="text-center pt-4 border-t border-accent/20">
          <p class="text-muted text-sm">
            Don't have an account?
            <router-link to="/register" class="text-primary font-semibold hover:text-accent transition">
              Create one
            </router-link>
          </p>
        </div>
      </form>

      <!-- Info Card -->
      <div class="mt-6 p-4 bg-white rounded-xl border border-accent/20">
        <p class="text-xs text-muted text-center">
          Demo: Use any email/password for testing. In production, credentials are verified.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/authStore'
import { useToast } from 'vue-toastification'

const router = useRouter()
const authStore = useAuthStore()
const toast = useToast()

const formData = ref({
  email: '',
  password: '',
  rememberMe: false
})

const errors = ref({
  email: '',
  password: ''
})

const error = ref('')
const isLoading = ref(false)

const validateField = (field) => {
  errors.value[field] = ''
  
  if (field === 'email') {
    if (!formData.value.email) {
      errors.value.email = 'Email is required'
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.value.email)) {
      errors.value.email = 'Please enter a valid email'
    }
  }
  
  if (field === 'password') {
    if (!formData.value.password) {
      errors.value.password = 'Password is required'
    } else if (formData.value.password.length < 6) {
      errors.value.password = 'Password must be at least 6 characters'
    }
  }
}

const handleLogin = async () => {
  error.value = ''
  
  // Validate all fields
  validateField('email')
  validateField('password')
  
  if (errors.value.email || errors.value.password) {
    return
  }
  
  isLoading.value = true
  
  try {
    const response = await authStore.login(
      formData.value.email,
      formData.value.password
    )
    
    if (response.success) {
      toast.success('Login successful!')
      
      // Redirect based on role
      const redirectPath = authStore.isAdmin ? '/admin/dashboard'
        : authStore.isStaff ? '/staff/dashboard'
        : '/dashboard'
      
      router.push(redirectPath)
    }
  } catch (err) {
    error.value = err || 'Login failed. Please try again.'
    toast.error(error.value)
  } finally {
    isLoading.value = false
  }
}
</script>
