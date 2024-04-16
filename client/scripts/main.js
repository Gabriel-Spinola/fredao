/// <reference path="types.d.ts" />

const pageUrl = "http://localhost:80/fredao/client/index.html/"
const apiBaseUrl = "http://localhost/fredao/api/";
const submitter = document.querySelector("button[value=enviar]")
const sessionId = null
const session_token_field = 'session_token'

// IDEA - when logged in api return session id, then we use it to check if we're logged in
async function handleLogin() {
    if (await checkSession()) {
        alert('Você já está logado')

        return
    }

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

            console.log(data)
            throw new Error("Response's not okay")
        }

        /**
         * @type {FrontFredao.APIResponse}
         */
        const data = await response.json()
        const token = data.message

        sessionStorage.setItem(session_token_field, token);
        console.log('SESSION: ' + sessionStorage.getItem(session_token_field))
        
        window.location.replace(window.location.replace(`${pageUrl}home`))
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

    /**
     * @type {FrontFredao.UserInfo}
     */
    const bodyData = { username, password }
    try {        
        const response = await fetch(`${apiBaseUrl}user/`, {
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
        window.location.replace(`${pageUrl}#login-box`)
    } catch (e) {
        console.error(e)
    }
}

/**
 * @returns {Promise<boolean>}
 */
async function checkSession() {
    const session_token = sessionStorage.getItem(session_token_field)
    if (session_token === null) {
        console.log("no session")
        return false
    }

    const tokenData = { key: session_token }

    try {
        const response = await fetch(`${apiBaseUrl}auth`, {
            method: 'POST',
            body: JSON.stringify(tokenData)
        })

        if (!response.ok) {
            const data = await response.json()
            console.error("Response's not okay: ", JSON.stringify(data))

            throw new Error("Response's not okay")
        }

        /**
         * @type {FrontFredao.APIResponse}
         */
        const data = await response.json()
        console.log(data.message)

        return true
    } catch (e) {
        console.error(e)
    }

    return false
}

function removeCurrentSession() {
    sessionStorage.removeItem(session_token_field)
    sessionStorage.removeItem(session_id_field)
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

/// NOTE - Not implemented
/**
 * @template Params
 * @param {(params: Params) => Promise<Response>} request 
 * @returns {FrontFredao.FrabricatedResponse}
 */
function requestFactory(request) {
    return async (params) => {
        try {
            const response = await request(params)

            if (!response.ok) {
                const { message } = await response.json()

                console.error("Response's not okay: ", JSON.stringify(message))
                throw new Error("Response's not okay")
            }

            const { message } = await response.json()
            return message
        } catch (error) {
            console.error('Request failed: ', JSON.stringify(error))
        }
    }
}