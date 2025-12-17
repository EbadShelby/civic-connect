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
      class="shadow-primary/5 animate-fade-in-up relative z-10 w-full max-w-md rounded-2xl border border-gray-100 bg-white p-8 shadow-xl"
    >
      <!-- Logo -->
      <div class="mb-8 flex justify-center">
        <router-link to="/" class="group flex items-center gap-2">
          <div
            class="bg-primary shadow-primary/20 group-hover:bg-primary-hover rounded-xl p-2 text-white shadow-lg transition-all duration-300"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke-width="2"
              stroke="currentColor"
              class="h-8 w-8"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"
              />
            </svg>
          </div>
        </router-link>
      </div>

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
