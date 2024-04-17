/// <reference path="types/types.d.ts" />
/// <reference path="types/toasts.d.ts" />

import { checkSession, apiBaseURL, absoluteURL, initSession } from "./main.js"
import { toastError, toastSuccess, toastWarn } from "./toasts.js"

const submitter = document.querySelector("button[value=enviar]")

document.querySelector("#login-form input[name=submit]").addEventListener('click', async function(event) {
    event.preventDefault()
    
    if (await checkSession()) {
        toastWarn("Você já está logado")

        return
    }

    const form = document.getElementById("login-form")
    
    const formData = new FormData(form, submitter)
    const username = formData.get("username")
    const password = formData.get("password")

    if (username === '' || password === '') {
        toastError("Usuário ou senha não podem estar vazios")

        return;
    }
    
    /**
     * @type {FrontFredao.UserInfo}
     */
    const bodyData = { username, password }
    try {        
        const response = await fetch(`${apiBaseURL}user/login`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(bodyData),
        })

        if (!response.ok) {
            const data = await response.json()
            console.error("Response's not okay: ", JSON.stringify(data))

            const { status, message } = data 
            if (status === 404) {
                console.error(message)
            } 

            console.log(data)
            throw new Error("Response's not okay")
        }

        /**
         * @type {FrontFredao.APIResponse}
         */
        const data = await response.json()
        const token = data.message

        initSession(token)
        
        window.location.replace(`${absoluteURL}home.html`)
    } catch (e) {
        console.error(e)
        toastError("Não foi possível efetuar o login")
    }
})

document.querySelector("#register-form input[name=submit]").addEventListener('click', async function(event) {
    const form = document.getElementById("register-form")

    const formData = new FormData(form, submitter)
    const username = formData.get("username")
    const password = formData.get("password")
    const confirmPass = formData.get("confirm-password")

    if (username === '' || password === '') {
        toastError("Usuário ou senha não podem estar vazios")

        return;
    }

    if (password !== confirmPass) {
        toastError("As senhas não batem")
    }

    /**
     * @type {FrontFredao.UserInfo}
     */
    const bodyData = { username, password }
    try {        
        const response = await fetch(`${apiBaseURL}user/`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(bodyData),
        })

        if (!response.ok) {
            const data = await response.json()
            console.error("Response's not okay: ", JSON.stringify(data))

            const { message } = data 
            throw new Error("Response's not okay " + message)
        }

        /**
         * @type {FrontFredao.APIResponse}
         */
        const data = await response.json()
        console.log(JSON.stringify(data))
        window.location.replace(`${absoluteURL}#login-box`)

        toastSuccess("Conta criado com succeso, faça login para cotinuar")
    } catch (e) {
        console.error(e)
        toastError("Essa nome de usuário é existente, ou houve algum erro ao criar sua conta")
    }
})