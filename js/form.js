const wishes = document.querySelectorAll('input[name="wishes[]"]')
const accepteds = document.querySelectorAll('select[name="accepted"] option')
const acceptedsContainer = document.querySelector('span[data-name="accepted"]')
const wishesContainer = document.querySelector('span[data-name="wishes"]')
//set default attributes to all wishes options
wishes.forEach(wish => { 
    wish.setAttribute('data-checked', 'false')
    wish.setAttribute('data-same-as-accepted', 'false')
})
//set default attribute to 1st wish option, because the accepted box is always the 1st one
wishes[0].setAttribute('data-same-as-accepted', 'true')
wishes[0].setAttribute('disabled', 'disabled')
//set default attributes to accepted boxes
accepteds.forEach(accepted => { 
    accepted.setAttribute('data-checked', 'false')
})
//add listeners to accepted and wish inputs and set attributes if user select some option and call the functions
acceptedsContainer.addEventListener('change', e => {
    accepteds.forEach(accepted => {
        if (accepted.selected) {
            accepted.setAttribute('data-checked', 'true')
        } else {
            accepted.setAttribute('data-checked', 'false')
        }
    })
    selectInputs()
    disableInputs()
})
wishesContainer.addEventListener('change', e => {
    wishes.forEach(wish => {
        if (wish.checked) {
            wish.setAttribute('data-checked', 'true')
        } else {
            wish.setAttribute('data-checked', 'false')
        }
    })
    selectInputs()
    disableInputs()
})
//store updated dom changes in variables
function selectInputs() {
    checkedAcceptedBox = document.querySelector('select[name="accepted"] option[data-checked="true"]')
    checkedWishBoxes = document.querySelectorAll('input[data-checked="true"]')
    notCheckedWishBoxes = document.querySelectorAll('input[data-checked="false"]')
}
//disable or undisable inputs
function disableInputs() {
    if (checkedAcceptedBox != null) {
        wishes.forEach(wish => {
            if (wish.value == checkedAcceptedBox.textContent) {
                wish.setAttribute('data-checked', 'false')
                wish.setAttribute('data-same-as-accepted', 'true')
                wish.checked = false
                wish.setAttribute('disabled', 'disabled')
            } else {
                wish.removeAttribute('disabled')
                wish.setAttribute('data-same-as-accepted', 'false')
            }
        })
    }
    selectInputs()
    if (checkedWishBoxes.length >= 3) {
        notCheckedWishBoxes.forEach(notCheckedWishBox => {
            notCheckedWishBox.setAttribute('disabled', 'disabled')
        })
    } else {
        notCheckedWishBoxes.forEach(notCheckedWishBox => {
            if (notCheckedWishBox.getAttribute('data-same-as-accepted') != 'true') {
                notCheckedWishBox.removeAttribute('disabled')
            }
        })
    }
}
