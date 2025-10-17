<script setup>
import { computed, onMounted, ref } from 'vue';
import { useFormField } from '../composables/form-field';

const props = defineProps(['title'])

const { value: emailField } = useFormField('registration_form_email')

const error = computed(() => emailField.value == '' || /@/.test(emailField.value) ? false : 'Pas un email');

</script>

<template>
    <h1>{{ title }}</h1>
    <fieldset>
        <slot></slot>
    </fieldset>
    <div v-if="error" style="color: red">{{  error  }}</div>
</template>
