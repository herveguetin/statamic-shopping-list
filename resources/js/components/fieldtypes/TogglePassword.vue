<template>
    <div>
        <text-input :type="inputType" :value="value" @input="updateDebounced"/>
        <label><input type="checkbox" v-model="show"/> Show Password</label>
    </div>
</template>

<script>
export default {
    mixins: [Fieldtype],
    data() {
        return {
            show: false
        }
    },
    computed: {
        inputType() {
            return this.show ? 'text' : 'password'
        }
    }
}
</script>
