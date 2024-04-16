/// <reference path="types.d.ts" />

export const absoluteURL = "http://localhost:80/fredao/client/"
export const apiBaseURL = "http://localhost/fredao/api/";

const sessionIdField = 'session_id'
const sessionTokenField = 'session_token'

// IDEA - when logged in api return session id, then we use it to check if we're logged in

/**
 * @returns {Promise<boolean>}
 */
export async function checkSession() {
    const session_token = getToken() 
    if (!session_token) {
        return false
    }

    const tokenData = { key: session_token }

    try {
        const response = await fetch(`${apiBaseURL}auth`, {
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

/**
 * @returns {string | false}
 */
export function getToken() {
    const sessionToken = sessionStorage.getItem(sessionTokenField)
    if (sessionToken === null) {
        console.log("no session")
        return false
    }

    return sessionToken
}

/**
 * 
 * @param {string} token 
 */
export function initSession(token) {
    sessionStorage.setItem(sessionTokenField, token)
}

export function removeCurrentSession() {
    sessionStorage.removeItem(sessionTokenField)
    sessionStorage.removeItem(sessionIdField)
}


export function redirectToSignin() {
    
}

(async () => {
    try {        
        const response = await fetch(`${apiBaseURL}`)

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
export function requestFactory(request) {
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
