<template>
  <div class="min-h-screen bg-bg flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-md">
      <!-- Logo & Header -->
      <div class="text-center mb-8">
        <div class="flex items-center justify-center w-12 h-12 bg-accent/10 rounded-full mx-auto mb-4">
          <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
          </svg>
        </div>
        <h2 class="text-2xl font-bold text-primary mb-2">Reset Password</h2>
        <p class="text-muted">Enter your email to receive password reset instructions</p>
      </div>

      <!-- Password Reset Form -->
      <form @submit.prevent="handleResetRequest" class="bg-white rounded-xl shadow-md p-8 space-y-6">
        <!-- Step 1: Email Input -->
        <div v-if="currentStep === 'email'">
          <label for="email" class="block text-sm font-medium text-primary mb-2">
            Email Address
          </label>
          <input
            id="email"
            v-model="formData.email"
            type="email"
            placeholder="you@example.com"
            class="w-full px-4 py-2 border border-accent/30 rounded-lg focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent transition"
            @blur="validateField('email')"
          />
          <p v-if="errors.email" class="mt-1 text-sm text-danger">{{ errors.email }}</p>
        </div>

        <!-- Step 2: Reset Code Input -->
        <div v-if="currentStep === 'code'">
          <p class="text-sm text-muted mb-4">
            We sent a reset code to <span class="font-semibold text-primary">{{ maskEmail(formData.email) }}</span>
          </p>
          <label for="resetCode" class="block text-sm font-medium text-primary mb-2">
            Reset Code
          </label>
          <input
            id="resetCode"
            v-model="formData.resetCode"
            type="text"
            placeholder="Enter 6-digit code"
            maxlength="6"
            class="w-full px-4 py-3 text-center text-2xl tracking-widest border border-accent/30 rounded-lg focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent transition"
            @input="formData.resetCode = formData.resetCode.replace(/\D/g, '').slice(0, 6)"
            @blur="validateField('resetCode')"
          />
          <p v-if="errors.resetCode" class="mt-1 text-sm text-danger">{{ errors.resetCode }}</p>
        </div>

        <!-- Step 3: New Password Input -->
        <div v-if="currentStep === 'password'">
          <label for="newPassword" class="block text-sm font-medium text-primary mb-2">
            New Password
          </label>
          <input
            id="newPassword"
            v-model="formData.newPassword"
            type="password"
            placeholder="••••••••"
            class="w-full px-4 py-2 border border-accent/30 rounded-lg focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent transition"
            @blur="validateField('newPassword')"
          />
          <p v-if="errors.newPassword" class="mt-1 text-sm text-danger">{{ errors.newPassword }}</p>

          <label for="confirmPassword" class="block text-sm font-medium text-primary mb-2 mt-4">
            Confirm Password
          </label>
          <input
            id="confirmPassword"
            v-model="formData.confirmPassword"
            type="password"
            placeholder="••••••••"
            class="w-full px-4 py-2 border border-accent/30 rounded-lg focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent transition"
            @blur="validateField('confirmPassword')"
          />
          <p v-if="errors.confirmPassword" class="mt-1 text-sm text-danger">{{ errors.confirmPassword }}</p>
        </div>

        <!-- Error Message -->
        <div v-if="error" class="p-4 bg-danger/10 border border-danger/30 rounded-lg">
          <p class="text-sm text-danger">{{ error }}</p>
        </div>

        <!-- Resend Code (Step 2) -->
        <div v-if="currentStep === 'code'" class="text-center text-sm text-muted">
          <p v-if="!isResendDisabled">
            Didn't receive the code?
            <button
              type="button"
              @click="handleResendCode"
              :disabled="isResending"
              class="text-primary font-semibold hover:text-accent transition disabled:opacity-50"
            >
              <span v-if="!isResending">Resend</span>
              <span v-else>Sending...</span>
            </button>
          </p>
          <p v-else class="text-muted">
            Resend available in <span class="font-semibold">{{ resendCountdown }}s</span>
          </p>
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          :disabled="isLoading"
          class="w-full py-2 px-4 bg-primary text-white rounded-lg font-semibold hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed transition"
        >
          <span v-if="currentStep === 'email' && !isLoading">Send Reset Code</span>
          <span v-else-if="currentStep === 'code' && !isLoading">Verify Code</span>
          <span v-else-if="currentStep === 'password' && !isLoading">Reset Password</span>
          <span v-else>Processing...</span>
        </button>

        <!-- Back Button -->
        <button
          v-if="currentStep !== 'email'"
          type="button"
          @click="goBack"
          class="w-full py-2 px-4 border border-accent/30 text-primary rounded-lg font-semibold hover:bg-accent/5 transition"
        >
          Back
        </button>

        <!-- Back to Login -->
        <div class="text-center pt-4 border-t border-accent/20">
          <router-link to="/login" class="text-sm text-primary hover:text-accent transition">
            Back to Login
          </router-link>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'

const router = useRouter()
const toast = useToast()

const currentStep = ref('email') // email, code, password
const formData = ref({
  email: '',
  resetCode: '',
  newPassword: '',
  confirmPassword: ''
})

const errors = ref({
  email: '',
  resetCode: '',
  newPassword: '',
  confirmPassword: ''
})

const error = ref('')
const isLoading = ref(false)
const isResending = ref(false)
const isResendDisabled = ref(false)
const resendCountdown = ref(0)

const maskEmail = (email) => {
  const [name, domain] = email.split('@')
  return name.slice(0, 2) + '*'.repeat(Math.max(0, name.length - 2)) + '@' + domain
}

const validateField = (field) => {
  errors.value[field] = ''
  
  if (field === 'email') {
    if (!formData.value.email) {
      errors.value.email = 'Email is required'
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.value.email)) {
      errors.value.email = 'Please enter a valid email'
    }
  }
  
  if (field === 'resetCode') {
    if (!formData.value.resetCode) {
      errors.value.resetCode = 'Reset code is required'
    } else if (formData.value.resetCode.length !== 6) {
      errors.value.resetCode = 'Please enter a 6-digit code'
    }
  }
  
  if (field === 'newPassword') {
    if (!formData.value.newPassword) {
      errors.value.newPassword = 'Password is required'
    } else if (formData.value.newPassword.length < 8) {
      errors.value.newPassword = 'Password must be at least 8 characters'
    } else if (!/(?=.*[a-z])/.test(formData.value.newPassword)) {
      errors.value.newPassword = 'Password must contain lowercase letters'
    } else if (!/(?=.*[A-Z])/.test(formData.value.newPassword)) {
      errors.value.newPassword = 'Password must contain uppercase letters'
    } else if (!/(?=.*\d)/.test(formData.value.newPassword)) {
      errors.value.newPassword = 'Password must contain numbers'
    }
  }
  
  if (field === 'confirmPassword') {
    if (!formData.value.confirmPassword) {
      errors.value.confirmPassword = 'Please confirm your password'
    } else if (formData.value.confirmPassword !== formData.value.newPassword) {
      errors.value.confirmPassword = 'Passwords do not match'
    }
  }
}

const handleResetRequest = async () => {
  error.value = ''
  
  if (currentStep.value === 'email') {
    validateField('email')
    if (errors.value.email) return
    
    isLoading.value = true
    try {
      // Call API to send reset code
      // await resetPasswordStore.sendResetCode(formData.value.email)
      toast.success('Reset code sent to your email!')
      currentStep.value = 'code'
      isResendDisabled.value = true
      resendCountdown.value = 60
      
      const countdown = setInterval(() => {
        resendCountdown.value--
        if (resendCountdown.value <= 0) {
          isResendDisabled.value = false
          clearInterval(countdown)
        }
      }, 1000)
    } catch (err) {
      error.value = err || 'Failed to send reset code'
      toast.error(error.value)
    } finally {
      isLoading.value = false
    }
  } else if (currentStep.value === 'code') {
    validateField('resetCode')
    if (errors.value.resetCode) return
    
    isLoading.value = true
    try {
      // Call API to verify reset code
      // await resetPasswordStore.verifyResetCode(formData.value.email, formData.value.resetCode)
      toast.success('Code verified!')
      currentStep.value = 'password'
    } catch (err) {
      error.value = err || 'Invalid reset code'
      toast.error(error.value)
    } finally {
      isLoading.value = false
    }
  } else if (currentStep.value === 'password') {
    validateField('newPassword')
    validateField('confirmPassword')
    if (errors.value.newPassword || errors.value.confirmPassword) return
    
    isLoading.value = true
    try {
      // Call API to reset password
      // await resetPasswordStore.resetPassword(
      //   formData.value.email,
      //   formData.value.resetCode,
      //   formData.value.newPassword
      // )
      toast.success('Password reset successfully!')
      router.push('/login')
    } catch (err) {
      error.value = err || 'Failed to reset password'
      toast.error(error.value)
    } finally {
      isLoading.value = false
    }
  }
}

const handleResendCode = async () => {
  isResending.value = true
  try {
    // Call API to resend reset code
    // await resetPasswordStore.sendResetCode(formData.value.email)
    toast.success('Reset code sent again!')
    isResendDisabled.value = true
    resendCountdown.value = 60
    
    const countdown = setInterval(() => {
      resendCountdown.value--
      if (resendCountdown.value <= 0) {
        isResendDisabled.value = false
        clearInterval(countdown)
      }
    }, 1000)
  } catch (err) {
    error.value = err || 'Failed to resend reset code'
    toast.error(error.value)
  } finally {
    isResending.value = false
  }
}

const goBack = () => {
  if (currentStep.value === 'code') {
    currentStep.value = 'email'
    formData.value.resetCode = ''
  } else if (currentStep.value === 'password') {
    currentStep.value = 'code'
    formData.value.newPassword = ''
    formData.value.confirmPassword = ''
  }
  error.value = ''
}
</script>
