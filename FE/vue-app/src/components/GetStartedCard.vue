<script setup lang="ts"></script>

<template>
  <section class="bg-white/90 rounded-2xl border border-stone-200 shadow-lg shadow-stone-100/80 p-6 sm:p-8 flex flex-col gap-6">
    <div class="space-y-2">
      <h1 class="text-2xl font-semibold text-emerald-700">Let's get started</h1>
      <p class="text-stone-600">Đăng ký ngay để bắt đầu tham gia chat với mọi người.</p>
    </div>

    <ul class="space-y-4" aria-label="Next steps">
      <li class="flex items-center gap-3">
        <span class="inline-grid place-items-center w-7 h-7 rounded-full border border-stone-200 shadow-sm">
          <span class="w-3 h-3 rounded-full bg-stone-300"></span>
        </span>
        <RouterLink :to="{name: 'register'}" class="text-sm text-stone-700">
          Đăng ký
          <a class="font-semibold text-emerald-600 hover:underline underline-offset-4">Tài khoản mới</a>
        </RouterLink>
      </li>
      <li class="flex items-center gap-3">
        <span class="inline-grid place-items-center w-7 h-7 rounded-full border border-stone-200 shadow-sm">
          <span class="w-3 h-3 rounded-full bg-stone-300"></span>
        </span>
        <RouterLink :to="{name: 'login'}" class="text-sm text-stone-700">
          Đăng nhập vào 
          <a class="font-semibold text-emerald-600 hover:underline underline-offset-4">LongkeChat</a>
        </RouterLink>
      </li>
    </ul>

    <div>
      <RouterLink :to="{name: 'chat'}" class="inline-flex items-center justify-center px-5 py-3 rounded-lg bg-emerald-600 text-white text-sm font-semibold border border-emerald-700 shadow hover:bg-emerald-700 hover:-translate-y-px hover:shadow-lg transition">
        Chat Ngay
      </RouterLink>
    </div>
  </section>
</template>
