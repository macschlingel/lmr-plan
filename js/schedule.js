document.addEventListener('DOMContentLoaded', function() {
    // Initialize drag-and-drop for tags and volunteers
    new Sortable(document.getElementById('tag-list'), {
        group: {
            name: 'shared',
            pull: 'clone',
            put: false
        },
        animation: 150,
        ghostClass: 'sortable-ghost',
        sort: false
    });

    new Sortable(document.getElementById('volunteer-list'), {
        group: {
            name: 'shared',
            pull: 'clone',
            put: true
        },
        animation: 150,
        ghostClass: 'sortable-ghost',
        sort: false
    });

    document.querySelectorAll('.store-list').forEach((ulElement) => {
        new Sortable(ulElement, {
            group: 'shared',
            animation: 150,
            ghostClass: 'sortable-ghost',
            draggable: '.draggable-item',
            onAdd: function(evt) {
                let draggedItem = evt.item;
                let tagId = draggedItem.getAttribute('data-tag-id');
                let volunteerId = draggedItem.getAttribute('data-volunteer-id');

                console.log('Dragged item:', draggedItem);
                console.log('Item inner HTML:', draggedItem.innerHTML);
                console.log('All attributes of the dragged item:', draggedItem.attributes);
                for (let attr of draggedItem.attributes) {
                    console.log(`Attribute ${attr.name}: ${attr.value}`);
                }
                console.log('Data attributes:', { tagId, volunteerId });

                if (tagId || volunteerId) {
                    let storeId = evt.to.closest('.droppable').getAttribute('data-store-id');
                    let date = evt.to.closest('.droppable').getAttribute('data-date');

                    if (tagId) {
                        evt.to.closest('.droppable').style.backgroundColor = draggedItem.style.backgroundColor;

                        if (!evt.to.closest('.droppable').querySelector('.remove-tag-btn')) {
                            let removeButton = document.createElement('button');
                            removeButton.classList.add('btn', 'btn-sm', 'btn-danger', 'remove-tag-btn');
                            removeButton.style.position = 'absolute';
                            removeButton.style.top = '0';
                            removeButton.style.right = '0';
                            removeButton.style.padding = '2px 5px';
                            removeButton.innerHTML = '&times;';
                            removeButton.setAttribute('data-store-id', storeId);
                            removeButton.setAttribute('data-date', date);
                            evt.to.closest('.droppable').appendChild(removeButton);
                            attachRemoveHandler(removeButton);
                        }

                        fetch('plan_editor.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                assign_day_tag: true,
                                date: date,
                                store_id: storeId,
                                tag_id: tagId,
                                is_ajax: 1
                            }),
                        })
                        .then(response => response.json().catch(() => {
                            console.error('Failed to parse JSON response:', response);
                            alert('Failed to assign tag: Server returned an unexpected response.');
                        }))
                        .then(data => {
                            if (data && !data.success) {
                                console.error('Failed to assign tag:', data.message);
                                alert('Failed to assign tag: ' + data.message);
                                evt.to.closest('.droppable').style.backgroundColor = '#ffffff';
                                evt.to.closest('.droppable').querySelector('.remove-tag-btn')?.remove();
                            } else {
                                console.log('Tag assigned successfully');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Error: ' + error.message);
                            evt.to.closest('.droppable').style.backgroundColor = '#ffffff';
                            evt.to.closest('.droppable').querySelector('.remove-tag-btn')?.remove();
                        });
                    }

                    if (volunteerId) {
                        draggedItem.setAttribute('data-store-id', storeId);
                        draggedItem.setAttribute('data-date', date);
                        draggedItem.setAttribute('data-volunteer-id', volunteerId);

                        evt.to.appendChild(draggedItem);

                        if (!draggedItem.querySelector('.delete-assignment-btn')) {
                            let deleteButton = document.createElement('button');
                            deleteButton.classList.add('delete-assignment-btn');
                            deleteButton.innerHTML = '&times;';
                            deleteButton.style.position = 'absolute';
                            deleteButton.style.top = '-5px';
                            deleteButton.style.right = '-5px';
                            deleteButton.style.padding = '0';
                            deleteButton.style.background = '#ff0000';
                            deleteButton.style.color = '#fff';
                            deleteButton.style.border = 'none';
                            deleteButton.style.borderRadius = '50%';
                            deleteButton.style.width = '20px';
                            deleteButton.style.height = '20px';
                            deleteButton.style.lineHeight = '18px';
                            deleteButton.style.textAlign = 'center';
                            deleteButton.style.cursor = 'pointer';
                            deleteButton.setAttribute('data-store-id', storeId);
                            deleteButton.setAttribute('data-date', date);
                            deleteButton.setAttribute('data-volunteer-id', volunteerId);
                            draggedItem.appendChild(deleteButton);
                            attachDeleteHandlerToButton(deleteButton);
                        }

                        fetch('plan_editor.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                assign_volunteer: true,
                                date: date,
                                store_id: storeId,
                                volunteer_id: volunteerId,
                                is_ajax: 1
                            }),
                        })
                        .then(response => response.json().catch(() => {
                            console.error('Failed to parse JSON response:', response);
                            alert('Failed to assign volunteer: Server returned an unexpected response.');
                        }))
                        .then(data => {
                            if (data && !data.success) {
                                console.error('Failed to assign volunteer:', data.message);
                                alert('Failed to assign volunteer: ' + data.message);
                                draggedItem.remove();
                            } else {
                                console.log('Volunteer assigned successfully');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Error: ' + error.message);
                            draggedItem.remove();
                        });
                    }
                }
            }
        });
    });

    function attachDeleteHandler() {
        document.querySelectorAll('.delete-assignment-btn').forEach(button => {
            button.removeEventListener('click', handleDeleteClick);
            button.addEventListener('click', handleDeleteClick);
        });
    }

    function attachDeleteHandlerToButton(button) {
        button.addEventListener('click', handleDeleteClick);
    }

    function handleDeleteClick(event) {
        event.stopPropagation();
        const pill = event.target.closest('.draggable-item');
        const storeId = pill.getAttribute('data-store-id');
        const date = pill.getAttribute('data-date');
        const volunteerId = pill.getAttribute('data-volunteer-id');

        console.log('Attempting to remove assignment:', { storeId, date, volunteerId });

        if (pill.classList.contains('deleting')) return; 
        pill.classList.add('deleting');

        fetch('plan_editor.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                remove_assignment: true,
                date: date,
                store_id: storeId,
                volunteer_id: volunteerId,
                is_ajax: 1
            }),
        })
        .then(response => response.json().catch(() => {
            console.error('Failed to parse JSON response:', response);
            alert('Failed to remove assignment: Server returned an unexpected response.');
        }))
        .then(data => {
            if (data && !data.success) {
                console.error('Failed to remove assignment:', data.message);
                alert('Failed to remove assignment: ' + data.message);
            } else {
                console.log('Assignment removed successfully');
                pill.remove();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error: ' + error.message);
        })
        .finally(() => {
            pill.classList.remove('deleting');
        });
    }

    attachDeleteHandler();

    // Variable to track event listener status
    let isAddingMonth = false;

    const addNextMonthButton = document.getElementById('add-next-month');

    // Clear all existing event listeners before adding the new one
    addNextMonthButton.replaceWith(addNextMonthButton.cloneNode(true));
    const freshButton = document.getElementById('add-next-month');

    // Add event listener with proper checks
    freshButton.addEventListener('click', function() {
        // Check if already processing a request
        if (isAddingMonth) return;
        
        isAddingMonth = true;
        freshButton.disabled = true;

        let lastMonth = document.querySelector('.month-section:last-of-type').getAttribute('data-month');

        console.log('Adding next month:', lastMonth);

        fetch('plan_editor.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                add_next_month: true,
                last_displayed_month: lastMonth,
                is_ajax: 1
            }),
        })
        .then(response => response.json().catch(() => {
            console.error('Failed to parse JSON response:', response);
            alert('Failed to add next month: Server returned an unexpected response.');
        }))
        .then(data => {
            if (data && !data.success) {
                console.error('Failed to add next month:', data.message);
                alert('Failed to add next month: ' + data.message);
            } else {
                console.log('Next month added successfully');
                document.getElementById('schedule').insertAdjacentHTML('beforeend', data.html);
                attachDeleteHandler();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error: ' + error.message);
        })
        .finally(() => {
            isAddingMonth = false;
            freshButton.disabled = false;
        });
    });
});