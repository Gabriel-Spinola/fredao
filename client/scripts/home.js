import { loadCurrentImage } from "./handleImageForm.js" 
import { checkSession, getToken, absoluteURL, apiBaseURL } from "./main.js"

window.onload = async () => {
    const loginPageUrl = `${absoluteURL}#login-box`

    // TODO - Replace by spinner
    const body = document.querySelector("body")
    body.hidden = true;
    
    try {
        const isAuthenticated = await checkSession()
        if (!isAuthenticated) {
            window.location.replace(loginPageUrl)
        }
    } catch (e) {
        window.location.replace(loginPageUrl)
    }

    body.hidden = false
    await loadCurrentImage()
}

document.getElementById("delete-user").addEventListener('click', async function(event) {
    const token = getToken()
    if (!token) {
        console.error("no token")

        return;
    }

    try {
        const response = await fetch(`${apiBaseURL}user/${token.replaceAll('/', '|')}`, {
            method: "DELETE",
            headers: { "Content-Type": "application/json" },
        })

        if (!response.ok) {
            const data = await response.json()
            console.error("Response's not okay: ", JSON.stringify(data))

            console.log(data)
            throw new Error("Response's not okay")
        }

        window.location.replace(`${absoluteURL}#login-box`)
    } catch (e) {
        console.error(e)

        alert("failed to delete current user")
    }
})
