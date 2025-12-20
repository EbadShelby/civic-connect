<template>
  <div
    class="bg-bg relative flex min-h-screen flex-col items-center justify-center overflow-hidden p-4"
  >
    <!-- Decorative background elements -->
    <div class="pointer-events-none absolute top-0 left-0 z-0 h-full w-full overflow-hidden">
      <div
        class="bg-primary/5 absolute -top-[20%] -right-[10%] h-[50%] w-[50%] rounded-full blur-3xl"
      ></div>
      <div
        class="bg-accent/5 absolute bottom-[0%] -left-[10%] h-[40%] w-[40%] rounded-full blur-3xl"
      ></div>
    </div>

    <div
      class="shadow-primary/5 animate-fade-in-up relative z-10 w-full max-w-lg rounded-2xl border border-gray-100 bg-white p-8 shadow-xl"
    >
      <!-- Page content -->
      <slot></slot>

      <div class="mt-8 text-center">
        <router-link
          to="/"
          class="text-text-light hover:text-primary group flex items-center justify-center gap-1 text-sm transition-colors"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
            class="h-4 w-4 transition-transform group-hover:-translate-x-1"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"
            />
          </svg>
          Back to Home
        </router-link>
      </div>
    </div>

    <div class="text-text-lighter relative z-10 mt-8 text-sm">
      &copy; {{ new Date().getFullYear() }} CivicConnect
    </div>
  </div>
</template>

<style scoped>
.animate-fade-in-up {
  animation: fadeInUp 0.5s ease-out forwards;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
