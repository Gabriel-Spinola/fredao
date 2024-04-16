/**
 * @returns {string}
 */
String.prototype.splice = function(idx, rem, str) {
    return this.slice(0, idx) + str + this.slice(idx + Math.abs(rem));
};

const imageSubmitter = document.querySelector("input[value=enviar]")

imageSubmitter.addEventListener("click", async function(event) {
    event.preventDefault()

    const form = document.getElementById("image-form")
    
    const formData = new FormData(form, imageSubmitter)
    const image = formData.get("image")

    const token = sessionStorage.getItem(session_token_field)
    if (!token) {
        console.error("no token")

        return;
    }

    const reader = new FileReader()

    reader.readAsDataURL(image)
    reader.onload = async function () {
        const base64Image = reader.result

        try {
            const response = await fetch(`${apiBaseUrl}image/${token.replaceAll('/', '|')}`, {
                method: "PUT",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ data: base64Image }),
            })

            if (!response.ok) {
                const data = await response.json()
                console.error("Response's not okay: ", JSON.stringify(data))

                console.log(data)
                throw new Error("Response's not okay")
            }

            console.log("Image Sent")
        } catch (e) {
            console.error(e)
        }
    }

    reader.onerror = function (error) {
        console.log('Error: ', error)
    }
})

async function loadCurrentImage() {
    const imgTag = document.getElementById("user-image")
    const token = sessionStorage.getItem(session_token_field)
    if (!token) {
        console.error("no token")

        return;
    }

    try {
        const response = await fetch(`${apiBaseUrl}image/${token.replaceAll('/', '|')}`, {
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
        imgTag.src = message.image.toString()
    } catch (e) {
        console.error(e)
    }
}