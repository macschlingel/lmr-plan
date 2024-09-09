document.addEventListener('DOMContentLoaded', () => {
    // Initialize drag-and-drop for volunteer list with cloning
    new Sortable(document.getElementById('volunteer-list'), {
        group: {
            name: 'shared',
            pull: 'clone', // Clone items when dragged
            put: false     // Prevent items from being dropped back into the list
        },
        animation: 150,
        ghostClass: 'sortable-ghost',
        sort: false // Disable sorting within the volunteer list
    });

    // Initialize drag-and-drop for each droppable area
    document.querySelectorAll('.droppable').forEach((element) => {
        new Sortable(element, {
            group: 'shared',
            animation: 150,
            ghostClass: 'sortable-ghost',
            onAdd: function (evt) {
                // Handle what happens when an item is dropped into a new list
                let volunteerId = evt.item.getAttribute('data-id');
                let storeId = evt.to.getAttribute('data-store-id');
                let date = evt.to.getAttribute('data-date');

                // Perform AJAX request to save the assignment
                saveAssignment(volunteerId, storeId, date);
            },
            onRemove: function (evt) {
                // Handle what happens when an item is removed from a list
                let volunteerId = evt.item.getAttribute('data-id');
                let storeId = evt.from.getAttribute('data-store-id');
                let date = evt.from.getAttribute('data-date');

                // Perform AJAX request to remove the assignment
                removeAssignment(volunteerId, storeId, date);
            }
        });
    });
});

// Function to save the assignment to the database
function saveAssignment(volunteerId, storeId, date) {
    fetch('save_assignment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'save',
            volunteer_id: volunteerId,
            store_id: storeId,
            date: date
        }),
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            alert('Failed to save assignment: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// Function to remove the assignment from the database
function removeAssignment(volunteerId, storeId, date) {
    fetch('save_assignment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'remove',
            volunteer_id: volunteerId,
            store_id: storeId,
            date: date
        }),
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            alert('Failed to remove assignment: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}