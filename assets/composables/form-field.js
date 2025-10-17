import { onMounted, onUnmounted, ref } from "vue"

export function useFormField(id) {
    const value = ref()

    onMounted(() => {
        const field = document.getElementById(id);
        value.value = field.value
        field.addEventListener('input', (event) => value.value = event.target.value)
    })

    onUnmounted(() => document.getElementById(id).removeEventListener('input'))

    return {
        value
    }
};
