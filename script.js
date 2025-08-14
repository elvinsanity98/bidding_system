function updateBids(data) {
    const items = document.querySelectorAll('.item');
    items.forEach(item => {
        const itemId = item.getAttribute('data-item-id');
        const bidSpan = item.querySelector('.bid-amount');
        const bidderSpan = item.querySelector('.bidder');
        const bidForm = item.querySelector('.bid-form');
        const lockedMessage = item.querySelector('.locked-message');
        if (data[itemId]) {
            bidSpan.textContent = parseFloat(data[itemId].amount).toFixed(2);
            bidderSpan.textContent = data[itemId].bidder;
            if (data[itemId].locked) {
                item.classList.add('locked');
                if (!lockedMessage) {
                    const newLockedMessage = document.createElement('p');
                    newLockedMessage.className = 'locked-message';
                    newLockedMessage.innerHTML = `Locked to: <span class="bidder">${data[itemId].bidder}</span> at ₱<span class="bid-amount">${parseFloat(data[itemId].amount).toFixed(2)}</span>`;
                    item.querySelector('.item-content').appendChild(newLockedMessage);
                }
                if (bidForm) {
                    bidForm.remove();
                }
            } else {
                item.classList.remove('locked');
                if (lockedMessage) {
                    lockedMessage.remove();
                }
                if (bidForm) {
                    const bidInput = bidForm.querySelector('input[name="bid_amount"]');
                    bidInput.min = parseFloat(data[itemId].amount) + 1;
                    bidInput.placeholder = `Bid Amount (min ₱${(parseFloat(data[itemId].amount) + 1).toFixed(2)})`;
                }
            }
        }
    });
}

function fetchUpdates() {
    fetch('updates.php')
        .then(response => response.json())
        .then(data => {
            updateBids(data);
        })
        .catch(error => console.error('Error fetching updates:', error));
}

function startPolling() {
    fetchUpdates();
    setInterval(fetchUpdates, 5000); // Poll every 5 seconds
}