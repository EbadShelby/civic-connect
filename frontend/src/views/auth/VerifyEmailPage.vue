<template>
  <div class="min-h-screen bg-bg flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-md">
      <!-- Logo & Header -->
      <div class="text-center mb-8">
        <div class="flex items-center justify-center w-12 h-12 bg-accent/10 rounded-full mx-auto mb-4">
          <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
          </svg>
        </div>
        <h2 class="text-2xl font-bold text-primary mb-2">Verify Your Email</h2>
        <p class="text-muted">We sent a verification code to your email address</p>
      </div>

      <!-- Verification Form -->
      <form @submit.prevent="handleVerify" class="bg-white rounded-xl shadow-md p-8 space-y-6">
        <!-- Email Display -->
        <div class="p-4 bg-accent/5 rounded-lg border border-accent/20">
          <p class="text-sm text-muted mb-1">Verification code sent to:</p>
          <p class="font-semibold text-primary">{{ displayEmail }}</p>
        </div>

        <!-- OTP Input -->
        <div>
          <label for="otpCode" class="block text-sm font-medium text-primary mb-2">
            Verification Code
          </label>
          <input
            id="otpCode"
            v-model="formData.otpCode"
            type="text"
            placeholder="Enter 6-digit code"
            maxlength="6"
            class="w-full px-4 py-3 text-center text-2xl tracking-widest border border-accent/30 rounded-lg focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent transition"
            @input="formData.otpCode = formData.otpCode.replace(/\D/g, '').slice(0, 6)"
            @blur="validateField('otpCode')"
          />
          <p v-if="errors.otpCode" class="mt-1 text-sm text-danger">{{ errors.otpCode }}</p>
          <p class="mt-2 text-xs text-muted text-center">
            Check your email for the verification code
          </p>
        </div>

        <!-- Error Message -->
        <div v-if="error" class="p-4 bg-danger/10 border border-danger/30 rounded-lg">
          <p class="text-sm text-danger">{{ error }}</p>
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          :disabled="isLoading || formData.otpCode.length < 6"
          class="w-full py-2 px-4 bg-primary text-white rounded-lg font-semibold hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed transition"
        >
          <span v-if="!isLoading">Verify Email</span>
          <span v-else>Verifying...</span>
        </button>

        <!-- Resend Code -->
        <div class="text-center">
          <p class="text-sm text-muted mb-2">
            Didn't receive the code?
          </p>
          <button
            v-if="!isResendDisabled"
            type="button"
            @click="handleResend"
            :disabled="isResending"
            class="text-primary font-semibold hover:text-accent transition disabled:opacity-50"
          >
            <span v-if="!isResending">Resend Code</span>
            <span v-else>Sending...</span>
          </button>
          <p v-else class="text-sm text-muted">
            Resend available in <span class="font-semibold">{{ resendCountdown }}s</span>
          </p>
        </div>

        <!-- Back to Login -->
        <div class="text-center pt-4 border-t border-accent/20">
          <router-link to="/login" class="text-sm text-primary hover:text-accent transition">
            Back to Login
          </router-link>
        </div>
      </form>

      <!-- Info Card -->
      <div class="mt-6 p-4 bg-white rounded-xl border border-accent/20">
        <p class="text-xs text-muted text-center">
          Check your spam folder if you don't see the verification email
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/authStore'
import { useToast } from 'vue-toastification'

const router = useRouter()
const authStore = useAuthStore()
const toast = useToast()

const formData = ref({
  otpCode: ''
})

const errors = ref({
  otpCode: ''
})

const error = ref('')
const isLoading = ref(false)
const isResending = ref(false)
const isResendDisabled = ref(false)
const resendCountdown = ref(0)
const registeredEmail = ref('')

const displayEmail = computed(() => {
  if (!registeredEmail.value) return 'your email'
  const [name, domain] = registeredEmail.value.split('@')
  return name.slice(0, 2) + '*'.repeat(name.length - 2) + '@' + domain
})

onMounted(() => {
  registeredEmail.value = sessionStorage.getItem('registeredEmail') || ''
  if (!registeredEmail.value) {
    // Fallback - could redirect to register if no email found
    toast.warning('Please register first to verify email')
  }
})

const validateField = (field) => {
  errors.value[field] = ''
  
  if (field === 'otpCode') {
    if (!formData.value.otpCode) {
      errors.value.otpCode = 'Verification code is required'
    } else if (formData.value.otpCode.length !== 6) {
      errors.value.otpCode = 'Please enter a 6-digit code'
    } else if (!/^\d+$/.test(formData.value.otpCode)) {
      errors.value.otpCode = 'Code must contain only numbers'
    }
  }
}

const handleVerify = async () => {
  error.value = ''
  
  validateField('otpCode')
  
  if (errors.value.otpCode) {
    return
  }
  
  isLoading.value = true
  
  try {
    const response = await authStore.verifyEmail(
      registeredEmail.value,
      formData.value.otpCode
    )
    
    if (response.success) {
      toast.success('Email verified successfully!')
      sessionStorage.removeItem('registeredEmail')
      
      // Redirect to login after short delay
      setTimeout(() => {
        router.push('/login')
      }, 1000)
    }
  } catch (err) {
    error.value = err || 'Verification failed. Please try again.'
    toast.error(error.value)
  } finally {
    isLoading.value = false
  }
}

const handleResend = async () => {
  error.value = ''
  isResending.value = true
  
  try {
    const response = await authStore.resendOTP(registeredEmail.value)
    
    if (response.success) {
      toast.success('Verification code sent!')
      
      // Disable resend button for 60 seconds
      isResendDisabled.value = true
      resendCountdown.value = 60
      
      const countdown = setInterval(() => {
        resendCountdown.value--
        if (resendCountdown.value <= 0) {
          isResendDisabled.value = false
          clearInterval(countdown)
        }
      }, 1000)
    }
  } catch (err) {
    error.value = err || 'Failed to resend code. Please try again.'
    toast.error(error.value)
  } finally {
    isResending.value = false
  }
}
</script>
