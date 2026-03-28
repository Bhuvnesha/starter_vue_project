import { toast } from 'vue3-toastify'

export const success = (msg) => {
    toast.success(msg)
}

export const error = (msg) => {
    toast.error(msg)
}