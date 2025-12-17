<template>
  <div class="bg-bg flex min-h-screen items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
    <div class="w-full max-w-lg">
      <!-- Logo & Header -->
      <div class="mb-8 text-center">
        <div class="mb-4 flex items-center justify-center gap-2">
          <div class="bg-primary flex h-10 w-10 items-center justify-center rounded-lg">
            <span class="text-lg font-bold text-white">CC</span>
          </div>
          <h1 class="text-primary text-2xl font-bold">CivicConnect</h1>
        </div>
        <h2 class="text-primary mb-2 text-2xl font-bold">Welcome Back</h2>
        <p class="text-muted">Sign in to your account to continue</p>
      </div>

      <!-- Login Form -->
      <form @submit.prevent="handleLogin" class="space-y-6 rounded-xl bg-white p-8 shadow-md">
        <!-- Email Field -->
        <div>
          <label for="email" class="text-primary mb-2 block text-sm font-medium">
            Email Address
          </label>
          <input
            id="email"
            v-model="formData.email"
            type="email"
            name="email"
            placeholder="you@example.com"
            class="border-accent/30 focus:border-accent focus:ring-accent w-full rounded-lg border px-4 py-2 transition focus:ring-1 focus:outline-none"
            @blur="validateField('email')"
          />
          <p v-if="errors.email" class="text-danger mt-1 text-sm">{{ errors.email }}</p>
        </div>

        <!-- Password Field -->
        <div>
          <div class="mb-2 flex items-center justify-between">
            <label for="password" class="text-primary block text-sm font-medium"> Password </label>
            <router-link
              to="/forgot-password"
              class="text-accent hover:text-primary text-sm transition"
            >
              Forgot password?
            </router-link>
          </div>
          <input
            id="password"
            v-model="formData.password"
            type="password"
            name="password"
            placeholder="••••••••"
            class="border-accent/30 focus:border-accent focus:ring-accent w-full rounded-lg border px-4 py-2 transition focus:ring-1 focus:outline-none"
            @blur="validateField('password')"
          />
          <p v-if="errors.password" class="text-danger mt-1 text-sm">{{ errors.password }}</p>
        </div>

        <!-- Remember Me -->
        <div class="flex items-center">
          <input
            id="remember"
            v-model="formData.rememberMe"
            type="checkbox"
            name="remember"
            class="text-primary focus:ring-accent border-accent/30 h-4 w-4 cursor-pointer rounded"
          />
          <label for="remember" class="text-muted ml-2 block cursor-pointer text-sm">
            Remember me
          </label>
        </div>

        <!-- Error Message -->
        <div v-if="error" class="bg-danger/10 border-danger/30 rounded-lg border p-4">
          <p class="text-danger text-sm">{{ error }}</p>
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          :disabled="isLoading"
          class="bg-primary hover:bg-primary/90 w-full rounded-lg px-4 py-2 font-semibold text-white transition disabled:cursor-not-allowed disabled:opacity-50"
        >
          <span v-if="!isLoading">Sign In</span>
          <span v-else>Signing in...</span>
        </button>

        <!-- Sign Up Link -->
        <div class="border-accent/20 border-t pt-4 text-center">
          <p class="text-muted text-sm">
            Don't have an account?
            <router-link
              to="/register"
              class="text-primary hover:text-accent font-semibold transition"
            >
              Create one
            </router-link>
          </p>
        </div>
      </form>

      <!-- Info Card -->
      <div class="border-accent/20 mt-6 rounded-xl border bg-white p-4">
        <p class="text-muted text-center text-xs">
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
  rememberMe: false,
})

const errors = ref({
  email: '',
  password: '',
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
    const response = await authStore.login(formData.value.email, formData.value.password)

    if (response.success) {
      toast.success('Login successful!')

      // Redirect based on role
      const redirectPath = authStore.isAdmin
        ? '/admin/dashboard'
        : authStore.isStaff
          ? '/staff/dashboard'
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
