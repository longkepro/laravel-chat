<script setup lang="ts">
import { reactive } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import { isAxiosError } from 'axios';
import { useToast } from 'vue-toastification';
import { useAuthStore } from '@/stores/auth';
import FormButton from '@/components/forms/FormButton.vue';
import FormError from '@/components/forms/FormError.vue';
import FormInput from '@/components/forms/FormInput.vue';
import FormLabel from '@/components/forms/FormLabel.vue';
import FormTitle from '@/components/forms/FormTitle.vue';
import SocialAuthButton from '@/components/forms/SocialAuthButton.vue';

const router = useRouter();
const toast = useToast();
const authStore = useAuthStore();

const form = reactive({
  username: '',
  email: '',
  password: '',
  passwordConfirmation: '',
});

const errors = reactive<{ username?: string; email?: string; password?: string; passwordConfirmation?: string }>({});

const validate = () => {
  errors.username = form.username ? '' : 'Please enter a username.';
  errors.email = form.email && /.+@.+\..+/.test(form.email) ? '' : 'Please enter a valid email.';
  errors.password = form.password ? '' : 'Please enter a password.';
  errors.passwordConfirmation = form.passwordConfirmation === form.password ? '' : 'Passwords do not match.';

  return !errors.username && !errors.email && !errors.password && !errors.passwordConfirmation;
};

const onSubmit = async () => {
  if (!validate()) return;

  // Clear previous errors
  errors.username = '';
  errors.email = '';
  errors.password = '';
  errors.passwordConfirmation = '';

  try {
    await authStore.register({
      username: form.username,
      email: form.email,
      password: form.password,
      password_confirmation: form.passwordConfirmation,
    });

    toast.success('Đăng ký tài khoản thành công.');
    router.push({ name: 'home' });
  } catch (error: unknown) {
    if (isAxiosError(error) && error.response?.status === 422) {
      const errorData = error.response.data as { errors?: Record<string, string[]>; message?: string };
      
      // Populate validation errors from API
      if (errorData?.errors) {
        if (errorData.errors.username) {
          errors.username = errorData.errors.username[0];
        }
        if (errorData.errors.email) {
          errors.email = errorData.errors.email[0];
        }
        if (errorData.errors.password) {
          errors.password = errorData.errors.password[0];
        }
      }
      
      // Show error toast
      const errorMessage = Object.values(errors).find(e => e) || errorData?.message || 'Đăng ký thất bại.';
      toast.error(`Đăng ký thất bại: ${errorMessage}`);
      return;
    }

    console.error('Registration error:', error);
    toast.error('Đăng ký thất bại. Vui lòng thử lại.');
  }
};
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="flex w-11/12 max-w-5xl h-[600px] bg-white rounded-lg shadow-lg overflow-hidden">
      <div
        class="w-1/2 bg-cover bg-center"
        style="background-image: url('/background-pictures/pexels-furkanelveren-27390908.jpg')"
      ></div>

      <form
        class="w-1/2 p-10 flex flex-col justify-center items-center bg-white/90 space-y-4"
        @submit.prevent="onSubmit"
      >
        <FormTitle>Sign up</FormTitle>

        <div class="w-4/5 space-y-1">
          <FormLabel for-id="username">
            <i class="fas fa-user mr-1"></i> Username:
          </FormLabel>
          <FormInput id="username" v-model="form.username" name="username" type="text" autocomplete="username" />
          <FormError :message="errors.username" />
        </div>

        <div class="w-4/5 space-y-1">
          <FormLabel for-id="email">
            <i class="fas fa-envelope mr-1"></i> Email:
          </FormLabel>
          <FormInput id="email" v-model="form.email" name="email" type="email" autocomplete="email" />
          <FormError :message="errors.email" />
        </div>

        <div class="w-4/5 space-y-1">
          <FormLabel for-id="password">
            <i class="fas fa-lock mr-1"></i> Password:
          </FormLabel>
          <FormInput
            id="password"
            v-model="form.password"
            name="password"
            type="password"
            autocomplete="new-password"
          />
          <FormError :message="errors.password" />
        </div>

        <div class="w-4/5 space-y-1">
          <FormLabel for-id="password_confirmation">
            <i class="fas fa-lock mr-1"></i> Re-enter Password:
          </FormLabel>
          <FormInput
            id="password_confirmation"
            v-model="form.passwordConfirmation"
            name="password_confirmation"
            type="password"
            autocomplete="new-password"
          />
          <FormError :message="errors.passwordConfirmation" />
        </div>

        <div class="w-full flex justify-center">
          <FormButton type="submit" class="w-4/5 mt-4 mb-2 h-12 text-base font-semibold">Sign up</FormButton>
        </div>

        <div class="flex gap-4">
          <SocialAuthButton auth-provider="google" logo="../logos/google-logo-png-google-icon-logo-png-transparent-svg-vector-bie-supply-14.png" />
          <SocialAuthButton auth-provider="facebook" logo="../logos/Facebook_Logo_2023.png" />
        </div>

        <div class="text-sm mt-4 text-gray-700">
          Already have an account?
          <RouterLink :to="{ name: 'login' }" class="text-blue-500 hover:underline">Log in</RouterLink>
        </div>
      </form>
    </div>
  </div>
</template>
