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
        <h2 class="text-2xl font-bold text-primary mb-2">Join Our Community</h2>
        <p class="text-muted">Create an account to start reporting and engaging</p>
      </div>

      <!-- Registration Form -->
      <form @submit.prevent="handleRegister" class="bg-white rounded-xl shadow-md p-8 space-y-4">
        <!-- First Name Field -->
        <div>
          <label for="firstName" class="block text-sm font-medium text-primary mb-2">
            First Name
          </label>
          <input
            id="firstName"
            v-model="formData.firstName"
            type="text"
            name="firstName"
            placeholder="John"
            class="w-full px-4 py-2 border border-accent/30 rounded-lg focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent transition"
            @blur="validateField('firstName')"
          />
          <p v-if="errors.firstName" class="mt-1 text-sm text-danger">{{ errors.firstName }}</p>
        </div>

        <!-- Last Name Field -->
        <div>
          <label for="lastName" class="block text-sm font-medium text-primary mb-2">
            Last Name
          </label>
          <input
            id="lastName"
            v-model="formData.lastName"
            type="text"
            name="lastName"
            placeholder="Doe"
            class="w-full px-4 py-2 border border-accent/30 rounded-lg focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent transition"
            @blur="validateField('lastName')"
          />
          <p v-if="errors.lastName" class="mt-1 text-sm text-danger">{{ errors.lastName }}</p>
        </div>

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

        <!-- Phone Field (Optional) -->
        <div>
          <label for="phone" class="block text-sm font-medium text-primary mb-2">
            Phone Number (Optional)
          </label>
          <input
            id="phone"
            v-model="formData.phone"
            type="tel"
            name="phone"
            placeholder="+1 (555) 000-0000"
            class="w-full px-4 py-2 border border-accent/30 rounded-lg focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent transition"
          />
        </div>

        <!-- Password Field -->
        <div>
          <label for="password" class="block text-sm font-medium text-primary mb-2">
            Password
          </label>
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
          <p class="mt-1 text-xs text-muted">
            At least 8 characters with uppercase, lowercase, and numbers
          </p>
        </div>

        <!-- Confirm Password Field -->
        <div>
          <label for="confirmPassword" class="block text-sm font-medium text-primary mb-2">
            Confirm Password
          </label>
          <input
            id="confirmPassword"
            v-model="formData.confirmPassword"
            type="password"
            name="confirmPassword"
            placeholder="••••••••"
            class="w-full px-4 py-2 border border-accent/30 rounded-lg focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent transition"
            @blur="validateField('confirmPassword')"
          />
          <p v-if="errors.confirmPassword" class="mt-1 text-sm text-danger">{{ errors.confirmPassword }}</p>
        </div>

        <!-- Terms Acceptance -->
        <div class="flex items-start gap-2 pt-2">
          <input
            id="terms"
            v-model="formData.acceptTerms"
            type="checkbox"
            name="terms"
            class="h-4 w-4 text-primary focus:ring-accent border-accent/30 rounded cursor-pointer mt-1"
          />
          <label for="terms" class="text-sm text-muted cursor-pointer">
            I agree to the
            <a href="#" class="text-primary hover:text-accent transition">Terms of Service</a>
            and
            <a href="#" class="text-primary hover:text-accent transition">Privacy Policy</a>
          </label>
        </div>
        <p v-if="errors.terms" class="text-sm text-danger">{{ errors.terms }}</p>

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
          <span v-if="!isLoading">Create Account</span>
          <span v-else>Creating account...</span>
        </button>

        <!-- Sign In Link -->
        <div class="text-center pt-4 border-t border-accent/20">
          <p class="text-muted text-sm">
            Already have an account?
            <router-link to="/login" class="text-primary font-semibold hover:text-accent transition">
              Sign in
            </router-link>
          </p>
        </div>
      </form>
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
  firstName: '',
  lastName: '',
  email: '',
  phone: '',
  password: '',
  confirmPassword: '',
  acceptTerms: false
})

const errors = ref({
  firstName: '',
  lastName: '',
  email: '',
  password: '',
  confirmPassword: '',
  terms: ''
})

const error = ref('')
const isLoading = ref(false)

const validateField = (field) => {
  errors.value[field] = ''
  
  if (field === 'firstName') {
    if (!formData.value.firstName) {
      errors.value.firstName = 'First name is required'
    } else if (formData.value.firstName.length < 2) {
      errors.value.firstName = 'First name must be at least 2 characters'
    }
  }
  
  if (field === 'lastName') {
    if (!formData.value.lastName) {
      errors.value.lastName = 'Last name is required'
    } else if (formData.value.lastName.length < 2) {
      errors.value.lastName = 'Last name must be at least 2 characters'
    }
  }
  
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
    } else if (formData.value.password.length < 8) {
      errors.value.password = 'Password must be at least 8 characters'
    } else if (!/(?=.*[a-z])/.test(formData.value.password)) {
      errors.value.password = 'Password must contain lowercase letters'
    } else if (!/(?=.*[A-Z])/.test(formData.value.password)) {
      errors.value.password = 'Password must contain uppercase letters'
    } else if (!/(?=.*\d)/.test(formData.value.password)) {
      errors.value.password = 'Password must contain numbers'
    }
  }
  
  if (field === 'confirmPassword') {
    if (!formData.value.confirmPassword) {
      errors.value.confirmPassword = 'Please confirm your password'
    } else if (formData.value.confirmPassword !== formData.value.password) {
      errors.value.confirmPassword = 'Passwords do not match'
    }
  }
}

const handleRegister = async () => {
  error.value = ''
  
  // Validate all fields
  validateField('firstName')
  validateField('lastName')
  validateField('email')
  validateField('password')
  validateField('confirmPassword')
  
  if (!formData.value.acceptTerms) {
    errors.value.terms = 'You must accept the terms and conditions'
  }
  
  if (
    errors.value.firstName ||
    errors.value.lastName ||
    errors.value.email ||
    errors.value.password ||
    errors.value.confirmPassword ||
    errors.value.terms
  ) {
    return
  }
  
  isLoading.value = true
  
  try {
    const response = await authStore.register({
      firstName: formData.value.firstName,
      lastName: formData.value.lastName,
      email: formData.value.email,
      phone: formData.value.phone || null,
      password: formData.value.password
    })
    
    if (response.success) {
      toast.success('Account created! Please verify your email.')
      
      // Store email for verification page
      sessionStorage.setItem('registeredEmail', response.email)
      
      router.push('/verify-email')
    }
  } catch (err) {
    error.value = err || 'Registration failed. Please try again.'
    toast.error(error.value)
  } finally {
    isLoading.value = false
  }
}
</script>
