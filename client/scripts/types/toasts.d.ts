declare namespace ToastedFred {
    export type Status = 'success' | 'failure' | 'warn' | 'promise'
    export type ToastElement = {container: Element, text: Element, bottom: Element }
}