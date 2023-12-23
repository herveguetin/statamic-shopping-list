<template>

    <div>
        <v-select
            :filterable="false"
            :options="options"
            :value="selectValue"
            @search="onSearch"
            @input="onUpdate"
            class="flex-1"
            append-to-body
        >
            <template slot="no-options">
                Type to search for ingredients...
            </template>
            <template slot="option" slot-scope="option">
                <div class="d-center">
                    {{ option.label }}
                </div>
            </template>
            <template slot="selected-option" slot-scope="option">
                <div class="selected d-center">
                    {{ option.label }}
                </div>
            </template>
        </v-select>
        <input type="hidden" :value="value"/>
    </div>

</template>

<script>
export default {

    mixins: [Fieldtype],

    data() {
        return {
            options: [],
            selectValue: ''
        }
    },

    mounted() {
        if (this.value !== '') {
            this.fetchFromId()
        }
    },

    methods: {
        onSearch(search, loading) {
            if (search.length > 2) {
                loading(true)
                this.search(loading, search, this)
            }
        },
        search: _.debounce((loading, search, vm) => {
            fetch('/ingredients/list/' + search)
                .then(response => {
                    response.json().then(json => (vm.options = json))
                    loading(false)
                })
        }, 350),
        async fetchFromId() {
            const response = await fetch('/ingredients/show/' + this.value)
            let result = await response.json()
            if (result.length !== 0) {
                this.selectValue = result['label']
            }
        },
        onUpdate(value) {
            if (value) {
                this.update(value.code)
                this.selectValue = value.label
            } else {
                this.update(null)
            }
        }
    }

}
</script>
