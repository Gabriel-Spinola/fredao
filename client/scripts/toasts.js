/// <reference path="types/toasts.d.ts" />

import { absoluteURL } from "./main.js"

const rootElement = document.body

/**
 * @param {string} message 
 * @param {ToastedFred.Status} status 
 * @returns {ToastedFred.ToastElement}
 */
function createToast(status) {
    /**
     * @type {ToastedFred.ToastElement}
     */
    const toastElement = {}

    toastElement.container = document.createElement('div')
    toastElement.container.className += 'toast'
    toastElement.container.id += 'active'

    toastElement.text = document.createElement('h3')
    
    toastElement.bottom = document.createElement('div')
    toastElement.bottom.id += `toast-bottom-${status}`

    toastElement.container.appendChild(toastElement.text)
    toastElement.container.appendChild(toastElement.bottom)

    rootElement.insertBefore(toastElement.container, rootElement.firstChild)

    return toastElement
}

/**
 * TODO - Implement ESResult API
 * @param {string} message 
 * @param {ToastedFred.Status} status 
 * @param {number} duration in milliseconds
 * @param {?() => Promise<void>} callback 
 * @param {string?} errorMessage 
 */
export async function showToast(
    message,
    status,
    duration, 
    errorMessage,
    callback
) {
    const toast = createToast(status)
    toast.text.innerHTML = 'loading...'

    toast.container.addEventListener('click', () => toast.container.remove())

    if (callback) {
        toast.bottom.id = 'toast-bottom-promise'
        try { 
            await callback()

            status = 'success'
            toast.bottom.id = 'toast-bottom-success'
        } catch (e) {
            status = 'failure'
            message = errorMessage ?? message
            toast.bottom.id = 'toast-bottom-failure'
        }
    }

    toast.text.innerHTML = message

    await new Promise((resolve) => setTimeout(resolve, duration))

    toast.container.remove()
}

/**
 * @param {string} message 
 * @param {number} duration In milliseconds (Default value = 3s)
 */
export async function toastError(message, duration = 3000) {
    const fred = document.createElement("img")
    fred.id += 'fred'
    fred.src = `${absoluteURL}assets/fredoa.png`

    /* REVIEW
    const audio = document.createElement("audio")
    audio.src = `${absoluteURL}assets/toasty.mp3`
    audio.autoplay = true
    audio.volume = 1

    rootElement.insertBefore(audio, rootElement.firstChild)
    */

    rootElement.insertBefore(fred, rootElement.firstChild)
    await showToast(message, 'failure', duration)
    
    fred.remove()
}

/**
 * @param {string} message 
 * @param {number} duration In milliseconds (Default value = 3s)
 */
export async function toastSuccess(message, duration = 2000) {
    await showToast(message, 'success', duration)
}

/**
 * @param {string} message 
 * @param {number} duration In milliseconds (Default value = 3s)
 */
export async function toastWarn(message, duration = 2000) {
    await showToast(message, 'warn', duration)
}
