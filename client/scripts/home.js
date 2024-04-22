/// <reference path="types/types.d.ts" />
/// <reference path="types/news.d.ts" />

import { checkSession, getToken, absoluteURL, apiBaseURL } from "./main.js"
import { toastError } from "./toasts.js";

const lorem = "Lorem ipsum dolor sit, amet consectetur adipisicing elit. Fugiat nulla, illum id, maiores nemo dolorum modi voluptas expedita enim ad ut temporibus corrupti blanditiis dicta. Ea recusandae qui amet. Delectus.";

/**
 * @type {Array<FrontFredao.News>}
 */
const placeholder = [
    { title: "Test1", description: lorem, content: 'a', image: "./assets/fredao.jpeg" },
    { title: "Test2", description: lorem, content: 'a', image: "./assets/fredao.jpeg" },
    { title: "Test3", description: lorem, content: 'a', image: "./assets/fredao.jpeg" },
    { title: "Test4", description: lorem, content: 'a', image: "./assets/fredao.jpeg" },
    { title: "Test5", description: lorem, content: 'a', image: "./assets/fredao.jpeg" },
    { title: "Test6", description: lorem, content: 'a', image: "./assets/fredao.jpeg" },
]

const main = document.querySelector('main')

/**
 * @param {FrontFredao.News} news
 * @returns {Element}
 */
export function loadNews(news) {
    const newsBox = document.createElement('article')
    newsBox.className += 'news-box'

    const leftContainer = document.createElement('div')
    leftContainer.className += 'left'

    const titleEl = document.createElement('h1')
    titleEl.innerHTML = news.title

    const descriptionEl = document.createElement('h3')
    descriptionEl.innerHTML = news.description

    const dateEl = document.createElement('small')
    dateEl.innerHTML = news.date ?? new Date().toDateString()

    leftContainer.appendChild(titleEl)
    leftContainer.appendChild(descriptionEl)
    leftContainer.appendChild(dateEl)

    const rightContainer = document.createElement('div')
    rightContainer.className += 'right'

    const imageEl = document.createElement('img')
    imageEl.src = news.image ?? './assets/fredao.jpeg'
    imageEl.width = 256

    rightContainer.appendChild(imageEl)

    newsBox.appendChild(leftContainer)
    newsBox.appendChild(rightContainer)

    main.appendChild(newsBox)

    return newsBox
}

/**
 * @returns {FrontFredao.News[]}
 */
async function fetchNews() {
    try {
        const response = await fetch(`${apiBaseURL}news`, {
            method: "GET",
            headers: { "Content-Type": "application/json" },
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
         * @type {FrontFredao.APIResponse<FrontFredao.News>}
         */
        const data = await response.json()
        const news = data.message

        return news
    } catch (e) {
        console.error(e)
        toastError("Failed to load news")
    }
}

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

    const news = await fetchNews()
    console.log(news)

    body.hidden = false
    document.getElementById("delete-user")?.addEventListener('click', async function(event) {
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

    for (let i = 0; i < placeholder.length; i++) {
        loadNews(placeholder[i])
        loadNews(placeholder[i])
    }    
}
