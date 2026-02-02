<script setup lang="ts">
import { useAttrs } from 'vue'

const props = withDefaults(
  defineProps<{
    modelValue?: string | number | null
    type?: string
    id?: string
    name?: string
    placeholder?: string
    autocomplete?: string
  }>(),
  {
    modelValue: '',
    type: 'text',
  }
)

const emit = defineEmits<{
  (e: 'update:modelValue', value: string | number | null): void
}>()

const attrs = useAttrs()

const onInput = (event: Event) => {
  const target = event.target as HTMLInputElement
  emit('update:modelValue', target.value)
}
</script>

<template>
  <input
    :id="props.id"
    :name="props.name"
    :type="props.type"
    :value="props.modelValue ?? ''"
    :placeholder="props.placeholder"
    :autocomplete="props.autocomplete"
    v-bind="attrs"
    :class="['w-full h-10 px-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-500', attrs.class]"
    @input="onInput"
  />
</template>
