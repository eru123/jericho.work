import { ref } from "vue";

const keys = ref([]);
const data = ref([]);

export const dialogs = () => {
    return data;
};

const createUniqueKey = () => {
    while (true) {
        var key =
            Math.random().toString(36).substring(2, 15) +
            Math.random().toString(36).substring(2, 15);
        if (keys.value.indexOf(key) === -1) {
            keys.value.push(key);
            return key;
        }
    }
};

const createDialog = (obj) => {
    obj.key = createUniqueKey();
    obj.close = () => remove(obj.key);
    data.value.push(obj);
    return obj;
};

export const clear = () => {
    data.value = [];
};

export const removeLast = () => {
    const el = data.value.pop();
    for (const key in keys.value) {
        if (keys.value[key] === el.key) {
            keys.value.splice(key, 1);
            break;
        }
    }
};

export const removeFirst = () => {
    const el = data.value.shift();
    for (const key in keys.value) {
        if (keys.value[key] === el.key) {
            keys.value.splice(key, 1);
            break;
        }
    }
};

export const remove = (key) => {
    const index = keys.value.indexOf(key);
    if (index > -1) {
        keys.value.splice(index, 1);
        data.value.splice(index, 1);
    }
    return true;
};

export const createInfo = (title, message, onOk = null) => {
    return createDialog({
        title,
        message,
        type: "info",
        onOk,
    });
};

export const createWarning = (title, message, onOk = null) => {
    return createDialog({
        title,
        message,
        type: "warning",
        onOk,
    });
};

export const createError = (title, message, onOk = null) => {
    return createDialog({
        title,
        message,
        type: "error",
        onOk,
    });
};

export const createConfirm = (title, message, onOk = null, onCancel = null) => {
    return createDialog({
        title,
        message,
        type: "confirm",
        onOk,
        onCancel,
    });
};

export const createPrompt = (title, text, onOk = null, onCancel = null) => {
    return createDialog({
        title,
        text,
        type: "prompt",
        onOk,
        onCancel,
    });
};

export const createCustom = (component) => {
    return createDialog({
        component,
        type: "custom",
    });
};

export const createLoading = (message = null) => {
    return createDialog({
        message,
        type: "loading",
    });
};

export default dialogs;
