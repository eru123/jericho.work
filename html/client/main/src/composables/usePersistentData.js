import { reactive, watch, toRef } from "vue"

const state = reactive({})

export const usePersistentData = (key, defaultValue) => {
    if (state[key]) {
        const data = toRef(state, key)
        watch(data, () => {
            window.localStorage.setItem(key, JSON.stringify(data.value))
        }, { deep: true })

        return data
    } else if (window.localStorage.getItem(key)) {
        state[key] = JSON.parse(window.localStorage.getItem(key))
        const data = toRef(state, key)
        watch(data, () => {
            window.localStorage.setItem(key, JSON.stringify(data.value))
        }, { deep: true })

        return data
    }

    state[key] = defaultValue
    window.localStorage.setItem(key, JSON.stringify(defaultValue))
    const data = toRef(state, key)
    watch(data, () => {
        window.localStorage.setItem(key, JSON.stringify(data.value))
    }, { deep: true })

    return data
}

export const deletePersistentData = (key) => {
    delete state[key]
    window.localStorage.removeItem(key)
}

export const clearPersistentData = () => {
    for (const key in state) {
        delete state[key]
        window.localStorage.removeItem(key)
    }
}

export default usePersistentData
