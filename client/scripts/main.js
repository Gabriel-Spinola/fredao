/// <reference path="types.d.ts" />

const apiBaseUrl = "http://localhost/fredao/api/";
const submitter = document.querySelector("button[value=enviar]")

async function handleLogin() {
    const form = document.getElementById("login-form")
    
    const formData = new FormData(form, submitter)
    const username = formData.get("username")
    const password = formData.get("password")

    if (username === '' || password === '') {
        alert("Usuário ou senha não podem estar vazios")

        return;
    }
    
    /**
     * @type {FrontFredao.UserInfo}
     */
    const bodyData = { username, password }
    
    try {        
        const response = await fetch(`${apiBaseUrl}user/login`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(bodyData),
        })

        if (!response.ok) {
            const data = await response.json()
            console.error("Response's not okay: ", JSON.stringify(data))

            const { status, message } = data 
            if (status === 404) {
                alert(message)
            } 

            throw new Error("Response's not okay")
        }

        const data = await response.json()
        console.log(JSON.stringify(data))
    } catch (e) {
        console.error(e)
    }
}

async function handleRegister() {
    const form = document.getElementById("register-form")

    const formData = new FormData(form, submitter)
    const username = formData.get("username")
    const password = formData.get("password")
    const confirmPass = formData.get("confirm-password")

    if (username === '' || password === '') {
        alert("Usuário ou senha não podem estar vazios")

        return;
    }

    if (password !== confirmPass) {
        alert("As senhas não batem")
    }
}

async function checkSession() {
    try {
        const response = await fetch(`${apiBaseUrl}user`)

        if (!response.ok) {
            const data = await response.json()
            console.error("Response's not okay: ", JSON.stringify(data))

            throw new Error("Response's not okay")
        }

        const data = await response.json()
        console.log(JSON.stringify(data))
    } catch (e) {
        console.error(e)
    }
}

const testApi = (async () => {
    try {        
        const response = await fetch(`${apiBaseUrl}`)

        if (!response.ok) {
            const data = await response.json()
            console.error("Response's not okay: ", JSON.stringify(data))
    
            throw new Error("Response's not okay")
        }

        const data = await response.json()
        console.log(JSON.stringify(data))
    } catch (e) {
        console.error(e)
    }
})()