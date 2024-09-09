document.addEventListener('DOMContentLoaded', function() {
    // Initialize drag-and-drop for tags and volunteers
    new Sortable(document.getElementById('tag-list'), {
        group: {
            name: 'shared',
            pull: 'clone', // Allow cloning of items when dragged
            put: false     // Prevent items from being dropped back into the list
        },
        animation: 150,
        ghostClass: 'sortable-ghost',
        sort: false // Disable sorting within the tag list
    });

    // Initialize drag-and-drop for volunteers list
    new Sortable(document.getElementById('volunteer-list'), {
        group: {
            name: 'shared',
            pull: 'clone', // Allow cloning of items when dragged
            put: true      // Allow items to be placed in the grid
        },
        animation: 150,
        ghostClass: 'sortable-ghost',
        sort: false // Disable sorting within the volunteer list
    });

    // Initialize drag-and-drop for each droppable area (cells in the plan table)
    document.querySelectorAll('.store-list').forEach((ulElement) => {
        new Sortable(ulElement, {
            group: 'shared',
            animation: 150,
            ghostClass: 'sortable-ghost',
            draggable: '.draggable-item', // Ensure only draggable-item class elements are dragged
            onAdd: function (evt) {
                let draggedItem = evt.item;
                let tagId = draggedItem.getAttribute('data-tag-id'); // Access tag ID from dragged item
                let volunteerId = draggedItem.getAttribute('data-volunteer-id'); // Access volunteer ID from dragged item

                // Log all metadata related to the dragged item
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
                        // Update cell background color immediately
                        evt.to.closest('.droppable').style.backgroundColor = draggedItem.style.backgroundColor;

                        // Add the remove button immediately
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
                            attachRemoveHandler(removeButton); // Attach click handler for the new remove button
                        }

                        // Send AJAX request to save the tag assignment
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
                                is_ajax: 1 // Include this parameter to mark the request as AJAX
                            }),
                        })
                        .then(response => response.json().catch(() => { // Catch JSON parse errors
                            console.error('Failed to parse JSON response:', response);
                            alert('Failed to assign tag: Server returned an unexpected response.');
                        }))
                        .then(data => {
                            if (data && !data.success) {
                                console.error('Failed to assign tag:', data.message);
                                alert('Failed to assign tag: ' + data.message);
                                // Revert changes if server fails
                                evt.to.closest('.droppable').style.backgroundColor = '#ffffff';
                                evt.to.closest('.droppable').querySelector('.remove-tag-btn')?.remove();
                            } else {
                                console.log('Tag assigned successfully');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Error: ' + error.message);
                            // Revert changes on error
                            evt.to.closest('.droppable').style.backgroundColor = '#ffffff';
                            evt.to.closest('.droppable').querySelector('.remove-tag-btn')?.remove();
                        });
                    }

                    if (volunteerId) {
                        // Explicitly set attributes on the dragged item (pill)
                        draggedItem.setAttribute('data-store-id', storeId);
                        draggedItem.setAttribute('data-date', date);
                        draggedItem.setAttribute('data-volunteer-id', volunteerId);

                        // Add the volunteer immediately
                        evt.to.appendChild(draggedItem);
                        
                        // Ensure the delete button is added immediately after the pill is dropped
                        if (!draggedItem.querySelector('.delete-assignment-btn')) {
                            let deleteButton = document.createElement('button');
                            deleteButton.classList.add('delete-assignment-btn');
                            deleteButton.innerHTML = '&times;';
                            deleteButton.style.position = 'absolute';
                            deleteButton.style.top = '-5px'; // Adjust as per your design
                            deleteButton.style.right = '-5px'; // Adjust as per your design
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
                            // Set attributes correctly on the delete button
                            deleteButton.setAttribute('data-store-id', storeId);
                            deleteButton.setAttribute('data-date', date);
                            deleteButton.setAttribute('data-volunteer-id', volunteerId);
                            draggedItem.appendChild(deleteButton);
                            attachDeleteHandlerToButton(deleteButton); // Attach click handler for the new delete button
                        }

                        // Send AJAX request to save the volunteer assignment
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
                                is_ajax: 1 // Include this parameter to mark the request as AJAX
                            }),
                        })
                        .then(response => response.json().catch(() => { // Catch JSON parse errors
                            console.error('Failed to parse JSON response:', response);
                            alert('Failed to assign volunteer: Server returned an unexpected response.');
                        }))
                        .then(data => {
                            if (data && !data.success) {
                                console.error('Failed to assign volunteer:', data.message);
                                alert('Failed to assign volunteer: ' + data.message);
                                // Remove volunteer pill if server fails
                                draggedItem.remove();
                            } else {
                                console.log('Volunteer assigned successfully');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Error: ' + error.message);
                            // Remove volunteer pill on error
                            draggedItem.remove();
                        });
                    }
                }
            }
        });
    });

    // Function to attach the remove handler to the "X" button
    function attachDeleteHandler() {
        document.querySelectorAll('.delete-assignment-btn').forEach(button => {
            // Remove existing event listeners to prevent double binding
            button.removeEventListener('click', handleDeleteClick);
            button.addEventListener('click', handleDeleteClick);
        });
    }

    // Function to attach delete handler to a specific button
    function attachDeleteHandlerToButton(button) {
        button.addEventListener('click', handleDeleteClick);
    }

    // Handler function for delete button click
    function handleDeleteClick(event) {
        event.stopPropagation(); // Prevent the click from affecting the parent element
        const pill = event.target.closest('.draggable-item');
        const storeId = pill.getAttribute('data-store-id');
        const date = pill.getAttribute('data-date');
        const volunteerId = pill.getAttribute('data-volunteer-id');

        // Log the attributes to ensure they are correct
        console.log('Attempting to remove assignment:', { storeId, date, volunteerId });

        // Check if already processing to prevent double calls
        if (pill.classList.contains('deleting')) return; 
        pill.classList.add('deleting'); // Add flag to indicate it's being processed

        // Send AJAX request to remove the assignment
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
                is_ajax: 1 // Include this parameter to mark the request as AJAX
            }),
        })
        .then(response => response.json().catch(() => { // Catch JSON parse errors
            console.error('Failed to parse JSON response:', response);
            alert('Failed to remove assignment: Server returned an unexpected response.');
        }))
        .then(data => {
            if (data && !data.success) {
                console.error('Failed to remove assignment:', data.message);
                alert('Failed to remove assignment: ' + data.message);
            } else {
                console.log('Assignment removed successfully');
                pill.remove(); // Remove the pill from the UI
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error: ' + error.message);
        })
        .finally(() => {
            pill.classList.remove('deleting'); // Remove the processing flag
        });
    }

    // Initialize delete handler for all "X" buttons on load
    attachDeleteHandler();

    // Toggle visibility of the tags bar
    const toggleTagsButton = document.getElementById('toggle-tags');
    if (toggleTagsButton) {
        toggleTagsButton.addEventListener('click', function() {
            const tagsBar = document.getElementById('tags-bar');
            tagsBar.style.display = tagsBar.style.display === 'none' ? 'block' : 'none';
        });
    }
});