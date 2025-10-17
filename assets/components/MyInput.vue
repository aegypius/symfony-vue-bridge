<script setup>
defineProps(['type', 'name', 'label'])
</script>

<template>
    <div>
        <input :type="type" :name="name" :placeholder="label"></input>
    </div>
</template>
