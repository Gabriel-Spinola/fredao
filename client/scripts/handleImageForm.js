import { getToken, apiBaseURL } from './main.js'
import { showToast, toastError } from './toasts.js'

const imageSubmitter = document.querySelector("input[value=enviar]")
imageSubmitter.addEventListener("click", async function(event) {
    event.preventDefault()

    const form = document.getElementById("image-form")
    
    const formData = new FormData(form, imageSubmitter)
    const image = formData.get("image")

    const token = getToken()
    if (!token) {
        console.error("no token")

        return;
    }

    const reader = new FileReader()

    reader.readAsDataURL(image)
    reader.onload = async function () {
        const base64Image = reader.result

        await showToast(
            "Imagem enviada com sucesso",
            'promise',
            2000,
            "houve um error ao enviar sua imagem",
            async () => {
                const response = await fetch(`${apiBaseURL}image/${token.replaceAll('/',  '|')}`, {
                    method: "PUT",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ data: base64Image }),
                })
    
                if (!response.ok) {
                    const data = await response.json()
    
                    console.log(data)
                    throw new Error("Response's not okay")
                }
            } 
        )

        window.location.reload()
    }

    reader.onerror = function (error) {
        toastError("Não foi possível enviar sua image")

        console.error(error)
    }
})

export async function loadCurrentImage() {
    const imgTag = document.getElementById("user-image")
    const token = getToken()
    if (!token) {
        console.error("no token")

        return;
    }

    try {
        const response = await fetch(`${apiBaseURL}image/${token.replaceAll('/', '|')}`, {
            method: "GET",
            headers: { "Content-Type": "application/json" },
        })

        if (!response.ok) {
            const data = await response.json()
            console.error("Response's not okay: ", JSON.stringify(data))

            console.log(data)
            throw new Error("Response's not okay")
        }

        /**
         * @type {{message: {image: string}}}
         */
        const { message } = await response.json()
        imgTag.src = message.image?.toString() ?? ''
    } catch (e) {
        await toastError("Não foi possível carregar sua imagem de perfil")

        console.error(e)
    }
}