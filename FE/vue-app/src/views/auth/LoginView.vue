<script setup lang="ts">
import { reactive } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import { isAxiosError } from 'axios';
import { useToast } from 'vue-toastification';
import { useAuthStore } from '@/stores/auth';
import FormButton from '@/components/forms/FormButton.vue';
import FormError from '@/components/forms/FormError.vue';
import FormInput from '@/components/forms/FormInput.vue';
import FormLabel from '@/components/forms/FormLabel.vue';
import FormTitle from '@/components/forms/FormTitle.vue';
import SocialAuthButton from '@/components/forms/SocialAuthButton.vue';

const form = reactive({
  username: '',
  password: '',
});

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const toast = useToast();

const errors = reactive<{ username?: string; password?: string }>({});

const validate = () => {
  errors.username = form.username ? '' : 'Please enter your username.';
  errors.password = form.password ? '' : 'Please enter your password.';
  return !errors.username && !errors.password;
};

async function onSubmit() {
  if (!validate()) {
    toast.error('Vui lòng điền đầy đủ thông tin.');
    return;
  }

  // Clear previous errors
  errors.username = '';
  errors.password = '';

  try {
    await authStore.login({
      username: form.username,
      password: form.password,
    });

    if (authStore.user) {
      toast.success('Đăng nhập thành công!');
      const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : null;
      router.push(redirect || { name: 'home' });
    }
  } catch (error: unknown) {
    if (isAxiosError(error) && error.response?.status === 422) {
      const errorData = error.response.data as { errors?: Record<string, string[]>; message?: string };
      
      // Clear errors first
      errors.username = '';
      errors.password = '';
      
      // Populate validation errors from API
      if (errorData?.errors) {
        if (errorData.errors.username) {
          errors.username = errorData.errors.username[0];
        }
        if (errorData.errors.password) {
          errors.password = errorData.errors.password[0];
        }
      }
      
      // If no specific field errors, show general message
      const errorMessage = errors.username || errors.password || errorData?.message || 'Đăng nhập thất bại.';
      toast.error(`Đăng nhập thất bại: ${errorMessage}`);
      return;
    }

    console.error('Login error:', error);
    toast.error('Đăng nhập thất bại. Vui lòng thử lại.');
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="flex w-11/12 max-w-5xl h-[600px] bg-white rounded-lg shadow-lg overflow-hidden">
      <div
        class="w-1/2 bg-cover bg-top"
        style="background-image: url('/background-pictures/pexels-furkanelveren-27390908.jpg')"
      ></div>

      <form
        class="w-1/2 p-10 flex flex-col justify-center items-center bg-white/90 space-y-6"
        @submit.prevent="onSubmit"
      >
        <FormTitle>Sign In</FormTitle>

        <div class="w-4/5 space-y-1">
          <FormLabel for-id="username">
            <i class="fas fa-user mr-2"></i>User name:
          </FormLabel>
          <FormInput
            id="username"
            v-model="form.username"
            name="username"
            type="text"
            autocomplete="username"
          />
          <FormError :message="errors.username" />
        </div>

        <div class="w-4/5 space-y-1">
          <FormLabel for-id="password">
            <i class="fas fa-lock mr-2"></i>Password:
          </FormLabel>
          <FormInput
            id="password"
            v-model="form.password"
            name="password"
            type="password"
            autocomplete="current-password"
          />
          <FormError :message="errors.password" />
        </div>

        <FormButton type="submit">Log In</FormButton>
        <div class="flex gap-4">
          <SocialAuthButton auth-provider="google" logo="../logos/google-logo-png-google-icon-logo-png-transparent-svg-vector-bie-supply-14.png" />
          <SocialAuthButton auth-provider="facebook" logo="../logos/Facebook_Logo_2023.png" />
        </div>
        <p class="text-sm mt-4">
          Don't have an account yet?
          <RouterLink :to="{ name: 'register' }" class="text-blue-500 hover:underline">Sign Up</RouterLink>
        </p>
      </form>
    </div>
  </div>
</template>
