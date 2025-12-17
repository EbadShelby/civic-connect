<template>
  <div class="min-h-screen bg-gradient-to-br from-[#ebede9] to-gray-100">
    <div class="mx-auto max-w-2xl px-4 py-8">
      <!-- Back Link -->
      <router-link
        to="/profile"
        class="mb-6 inline-flex items-center gap-2 font-semibold text-[#75a743] hover:underline"
      >
        <ArrowLeftIcon class="h-5 w-5" />
        Back to Profile
      </router-link>

      <div class="overflow-hidden rounded-xl bg-white shadow-md">
        <div class="border-b border-gray-200 bg-gray-50 px-8 py-6">
          <h1 class="text-2xl font-bold text-[#10141f]">Edit Profile</h1>
          <p class="mt-1 text-sm text-[#819796]">Update your personal information</p>
        </div>

        <div class="p-8">
          <Form @submit="onSubmit" :validation-schema="schema" class="flex flex-col gap-6">
            <!-- First Name -->
            <div>
              <label for="firstName" class="mb-2 block font-semibold text-[#10141f]"
                >First Name</label
              >
              <Field
                name="firstName"
                id="firstName"
                type="text"
                v-model="formData.firstName"
                class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-[#25562e] focus:outline-none"
                :class="{ 'border-red-500': errors.firstName }"
              />
              <ErrorMessage name="firstName" class="mt-1 text-sm text-red-500" />
            </div>

            <!-- Last Name -->
            <div>
              <label for="lastName" class="mb-2 block font-semibold text-[#10141f]"
                >Last Name</label
              >
              <Field
                name="lastName"
                id="lastName"
                type="text"
                v-model="formData.lastName"
                class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-[#25562e] focus:outline-none"
                :class="{ 'border-red-500': errors.lastName }"
              />
              <ErrorMessage name="lastName" class="mt-1 text-sm text-red-500" />
            </div>

            <!-- Phone -->
            <div>
              <label for="phone" class="mb-2 block font-semibold text-[#10141f]"
                >Phone Number</label
              >
              <Field
                name="phone"
                id="phone"
                type="tel"
                v-model="formData.phone"
                class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-[#25562e] focus:outline-none"
                :class="{ 'border-red-500': errors.phone }"
              />
              <ErrorMessage name="phone" class="mt-1 text-sm text-red-500" />
            </div>

            <!-- Email (Read Only) -->
            <div>
              <label class="mb-2 block font-semibold text-[#10141f]">Email Address</label>
              <div
                class="w-full rounded-lg border border-gray-200 bg-gray-100 px-4 py-2 text-gray-500"
              >
                {{ authStore.user?.email }}
              </div>
              <p class="mt-1 text-xs text-[#819796]">Email cannot be changed directly.</p>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-4 border-t border-gray-200 pt-6">
              <router-link
                to="/profile"
                class="rounded-lg px-6 py-2 font-semibold text-[#819796] hover:bg-gray-100"
              >
                Cancel
              </router-link>
              <button
                type="submit"
                :disabled="isSubmitting"
                class="flex items-center justify-center rounded-lg bg-[#25562e] px-8 py-2 font-bold text-white transition-transform hover:scale-105 hover:bg-[#1a3d21] disabled:opacity-50 disabled:hover:scale-100"
              >
                <ArrowPathIcon v-if="isSubmitting" class="mr-2 h-5 w-5 animate-spin" />
                {{ isSubmitting ? 'Saving...' : 'Save Changes' }}
              </button>
            </div>
          </Form>

          <!-- Error Message -->
          <div v-if="error" class="mt-6 rounded-lg bg-red-50 p-4 text-red-800">
            {{ error }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Form, Field, ErrorMessage, useForm } from 'vee-validate'
import * as yup from 'yup'
import { useAuthStore } from '../../stores/authStore'
import { useToast } from 'vue-toastification'
import { ArrowLeftIcon, ArrowPathIcon } from '@heroicons/vue/24/solid'

const router = useRouter()
const authStore = useAuthStore()
const toast = useToast()

const isSubmitting = ref(false)
const error = ref('')

const formData = ref({
  firstName: '',
  lastName: '',
  phone: '',
})

// Validation Schema
const schema = yup.object().shape({
  firstName: yup.string().required('First name is required'),
  lastName: yup.string().required('Last name is required'),
  phone: yup.string().nullable(),
})

// Initialize form data
onMounted(() => {
  if (authStore.user) {
    formData.value = {
      firstName: authStore.user.first_name,
      lastName: authStore.user.last_name,
      phone: authStore.user.phone || '',
    }
  }
})

// Using validaiton errors from component
const { errors } = useForm()

const onSubmit = async (values) => {
  isSubmitting.value = true
  error.value = ''

  try {
    await authStore.updateUserProfile(authStore.user.id, {
      first_name: values.firstName,
      last_name: values.lastName,
      phone: values.phone,
    })

    toast.success('Profile updated successfully')
    router.push('/profile')
  } catch (err) {
    error.value = err.message || 'Failed to update profile'
    toast.error(error.value)
  } finally {
    isSubmitting.value = false
  }
}
</script>
