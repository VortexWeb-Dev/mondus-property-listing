<footer class="w-4/5 mx-auto mb-8 px-4 text-center">
    &copy; <?php echo date("Y"); ?> <a href="https://vortexweb.cloud/" target="_blank">VortexWeb</a>
</footer>
<a href="current.php" class="opacity-0 text-xs">invisible link</a>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="./node_modules/lodash/lodash.min.js"></script>
<script src="./node_modules/dropzone/dist/dropzone-min.js"></script>
<script src="./node_modules/preline/dist/preline.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script src="./node_modules/lodash/lodash.min.js"></script>
<script src="./node_modules/apexcharts/dist/apexcharts.min.js"></script>
<script src="./node_modules/preline/dist/helper-apexcharts.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fabric@latest/dist/index.min.js"></script>
<script src="assets/js/script.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const isAdmin = localStorage.getItem('isAdmin') == 'true';

        if (!isAdmin) {
            document.querySelectorAll(".admin-only").forEach(el => el.style.display = "none");
        }
    });

    // Toggle Bayut and Dubizzle
    document.getElementById('toggle_bayut_dubizzle') && document.getElementById('toggle_bayut_dubizzle').addEventListener('change', function() {
        const isChecked = this.checked;
        document.getElementById('bayut_enable').checked = isChecked;
        document.getElementById('dubizzle_enable').checked = isChecked;
    });

    // Format date
    function formatDate(dateString) {
        const date = new Date(dateString);
        const options = {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        };
        return date.toLocaleDateString('en-US', options);
    }

    // Update character count
    function updateCharCount(countElement, length, maxLength) {
        titleCount = document.getElementById(countElement);
        titleCount.textContent = length;

        if (length >= maxLength) {
            titleCount.parentElement.classList.add('text-danger');
        } else {
            titleCount.parentElement.classList.remove('text-danger');
        }
    }

    // Parse and update location fields
    function updateLocationFields(location, type) {
        const locationParts = location.split('-');

        const city = locationParts[0].trim();
        const community = locationParts[1].trim();
        const subcommunity = locationParts[2].trim() || null;
        const building = locationParts[3].trim() || null;

        document.getElementById(`${type}_city`).value = city;
        document.getElementById(`${type}_community`).value = community;
        document.getElementById(`${type}_subcommunity`).value = subcommunity;
        document.getElementById(`${type}_building`).value = building;
    }

    // Update reference
    async function handleUpdateReference(event) {
        event.preventDefault();

        const formData = new FormData(event.target);
        const propertyId = formData.get('propertyId');
        const newReference = formData.get('newReference');

        try {
            const response = await fetch(`${API_BASE_URL}crm.item.update?entityTypeId=${LISTINGS_ENTITY_TYPE_ID}&id=${propertyId}&fields[ufCrm15ReferenceNumber]=${newReference}`);
            const data = await response.json();
            location.reload();
        } catch (error) {
            console.error('Error updating reference:', error);
        }
    }

    // Format input date
    function formatInputDate(dateInput) {
        if (!dateInput) return null;

        const date = new Date(dateInput);

        if (isNaN(date.getTime())) return null;

        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        return `${year}-${month}-${day}`;
    }

    // Get agent
    async function getAgent(agentId) {
        const response = await fetch(`${API_BASE_URL}crm.item.list?entityTypeId=${AGENTS_ENTITY_ID}&filter[ufCrm14AgentId]=${agentId}`);
        const data = await response.json();
        return data.result.items[0] || null;
    }

    // Handle action
    async function handleAction(action, propertyId, platform = null) {
        const baseUrl = API_BASE_URL;
        let apiUrl = '';
        let reloadRequired = true;

        switch (action) {
            case 'copyLink':
                const link = `https://lightgray-kudu-834713.hostingersite.com/property-listing-gi/index.php?page=view-property&id=${propertyId}`;
                navigator.clipboard.writeText(link);
                alert('Link copied to clipboard.');
                reloadRequired = false;
                break;

            case 'downloadPDF':
                window.location.href = `download-pdf.php?id=${propertyId}`;
                reloadRequired = false;
                break;
            case 'downloadPDFAgent':
                window.location.href = `download-pdf-agent.php?id=${propertyId}`;
                reloadRequired = false;
                break;
            case 'export-excel':
                window.location.href = `export-excel.php?id=${propertyId}`;
                reloadRequired = false;
                break;

            case 'duplicate':
                try {
                    const getUrl = `${baseUrl}/crm.item.get?entityTypeId=${LISTINGS_ENTITY_TYPE_ID}&id=${propertyId}&select[0]=id&select[1]=uf_*`;
                    const response = await fetch(getUrl, {
                        method: 'GET'
                    });
                    const data = await response.json();
                    const property = data.result.item;

                    let addUrl = `${baseUrl}/crm.item.add?entityTypeId=${LISTINGS_ENTITY_TYPE_ID}`;
                    for (const field in property) {
                        if (
                            field.startsWith('ufCrm15') &&
                            !['ufCrm15ReferenceNumber', 'ufCrm15TitleEn', 'ufCrm15Status', 'ufCrm15PhotoLinks', 'ufCrm15Documents', 'ufCrm15Notes'].includes(field)
                        ) {
                            addUrl += `&fields[${field}]=${encodeURIComponent(property[field])}`;
                        }
                    }

                    if (property['ufCrm15PhotoLinks']) {
                        property['ufCrm15PhotoLinks'].forEach((photoLink, index) => {
                            addUrl += `&fields[ufCrm15PhotoLinks][${index}]=${encodeURIComponent(photoLink)}`;
                        });
                    }

                    if (property['ufCrm15Documents']) {
                        property['ufCrm15Documents'].forEach((document, index) => {
                            addUrl += `&fields[ufCrm15Documents][${index}]=${encodeURIComponent(document)}`;
                        });
                    }

                    if (property['ufCrm15Notes']) {
                        property['ufCrm15Notes'].forEach((note, index) => {
                            addUrl += `&fields[ufCrm15Notes][${index}]=${encodeURIComponent(note)}`;
                        });
                    }

                    addUrl += `&fields[ufCrm15TitleEn]=${encodeURIComponent(property.ufCrm15TitleEn + ' (Duplicate)')}`;
                    addUrl += `&fields[ufCrm15ReferenceNumber]=${encodeURIComponent(property.ufCrm15ReferenceNumber) + '-duplicate'}`;
                    addUrl += `&fields[ufCrm15Status]=DRAFT`;

                    await fetch(addUrl, {
                        method: 'GET'
                    });
                } catch (error) {
                    console.error('Error duplicating property:', error);
                }
                break;

            case 'publish':
                apiUrl = `${baseUrl}/crm.item.update?entityTypeId=${LISTINGS_ENTITY_TYPE_ID}&id=${propertyId}&fields[ufCrm15Status]=PUBLISHED`;
                if (platform) {
                    apiUrl += `&fields[ufCrm15${platform.charAt(0).toUpperCase() + platform.slice(1)}Enable]=Y`;
                } else {
                    apiUrl += `&fields[ufCrm15PfEnable]=Y&fields[ufCrm15BayutEnable]=Y&fields[ufCrm15DubizzleEnable]=Y&fields[ufCrm15WebsiteEnable]=Y&fields[ufCrm15Status]=PUBLISHED`;
                }
                break;

            case 'unpublish':
                apiUrl = `${baseUrl}/crm.item.update?entityTypeId=${LISTINGS_ENTITY_TYPE_ID}&id=${propertyId}`;
                if (platform) {
                    apiUrl += `&fields[ufCrm15${platform.charAt(0).toUpperCase() + platform.slice(1)}Enable]=N`;
                } else {
                    apiUrl += `&fields[ufCrm15PfEnable]=N&fields[ufCrm15BayutEnable]=N&fields[ufCrm15DubizzleEnable]=N&fields[ufCrm15WebsiteEnable]=N&fields[ufCrm15Status]=UNPUBLISHED`;
                }
                break;

            case 'archive':
                if (confirm('Are you sure you want to archive this property?')) {
                    apiUrl = `${baseUrl}/crm.item.update?entityTypeId=${LISTINGS_ENTITY_TYPE_ID}&id=${propertyId}&fields[ufCrm15Status]=ARCHIVED`;
                } else {
                    reloadRequired = false;
                }
                break;

            case 'delete':
                if (confirm('Are you sure you want to delete this property?')) {
                    try {
                        // First get property details to find image URLs
                        const getPropertyUrl = `${baseUrl}/crm.item.get?entityTypeId=${LISTINGS_ENTITY_TYPE_ID}&id=${propertyId}`;
                        const propertyResponse = await fetch(getPropertyUrl);
                        const propertyData = await propertyResponse.json();

                        if (propertyData.result && propertyData.result.item) {
                            const property = propertyData.result.item;
                            console.log('Property data for deletion:', property);

                            // Delete images from S3
                            if (property.ufCrm15PhotoLinks && Array.isArray(property.ufCrm15PhotoLinks)) {
                                console.log('Found photo links:', property.ufCrm15PhotoLinks);
                                for (const imageUrl of property.ufCrm15PhotoLinks) {
                                    try {
                                        console.log('Attempting to delete image:', imageUrl);
                                        const response = await fetch('./delete-s3-object.php', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                            },
                                            body: JSON.stringify({
                                                fileUrl: imageUrl
                                            })
                                        });
                                        const result = await response.json();
                                        console.log('Delete response:', result);
                                        if (!result.success) {
                                            console.error(`Failed to delete image: ${result.error}`);
                                        }
                                    } catch (error) {
                                        console.error(`Error deleting S3 object: ${imageUrl}`, error);
                                    }
                                }
                            }

                            // Delete floorplan from S3 if exists
                            if (property.ufCrm15FloorPlan) {
                                try {
                                    console.log('Attempting to delete floorplan:', property.ufCrm15FloorPlan);
                                    const response = await fetch('./delete-s3-object.php', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                        },
                                        body: JSON.stringify({
                                            fileUrl: property.ufCrm15FloorPlan
                                        })
                                    });
                                    const result = await response.json();
                                    console.log('Floorplan delete response:', result);
                                    if (!result.success) {
                                        console.error(`Failed to delete floorplan: ${result.error}`);
                                    }
                                } catch (error) {
                                    console.error(`Error deleting S3 floorplan: ${property.ufCrm15FloorPlan}`, error);
                                }
                            }

                            // Delete documents from S3
                            if (property.ufCrm15Documents && Array.isArray(property.ufCrm15Documents)) {
                                console.log('Found documents:', property.ufCrm15Documents);
                                for (const docUrl of property.ufCrm15Documents) {
                                    try {
                                        console.log('Attempting to delete document:', docUrl);
                                        const response = await fetch('./delete-s3-object.php', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                            },
                                            body: JSON.stringify({
                                                fileUrl: docUrl
                                            })
                                        });
                                        const result = await response.json();
                                        console.log('Delete response:', result);
                                        if (!result.success) {
                                            console.error(`Failed to delete document: ${result.error}`);
                                        }
                                    } catch (error) {
                                        console.error(`Error deleting S3 document: ${docUrl}`, error);
                                    }
                                }
                            }
                        }

                        // Now delete the property from CRM
                        apiUrl = `${baseUrl}/crm.item.delete?entityTypeId=${LISTINGS_ENTITY_TYPE_ID}&id=${propertyId}`;
                    } catch (error) {
                        console.error('Error in delete process:', error);
                        reloadRequired = false;
                    }
                } else {
                    reloadRequired = false;
                }
                break;

            default:
                console.error('Invalid action:', action);
                reloadRequired = false;
        }

        if (apiUrl) {
            try {
                await fetch(apiUrl, {
                    method: 'GET'
                });
            } catch (error) {
                console.error(`Error executing ${action}:`, error);
            }
        }

        if (reloadRequired) {
            location.reload();
        }
    }

    // Bulk action
    async function handleBulkAction(action, platform) {
        const checkboxes = document.querySelectorAll('input[name="property_ids[]"]:checked');
        const propertyIds = Array.from(checkboxes).map(checkbox => checkbox.value);

        if (propertyIds.length === 0) {
            alert('Please select at least one property.');
            return;
        }

        if (confirm(`Are you sure you want to ${action} the selected properties?`)) {
            try {
                const baseUrl = API_BASE_URL;
                const apiUrl = `${baseUrl}/crm.item.${action === 'delete' ? 'delete' : 'update'}?entityTypeId=${LISTINGS_ENTITY_TYPE_ID}`;

                const platformFieldMapping = {
                    pf: 'ufCrm15PfEnable',
                    bayut: 'ufCrm15BayutEnable',
                    dubizzle: 'ufCrm15DubizzleEnable',
                    website: 'ufCrm15WebsiteEnable'
                };

                // If action is delete, first get all property details to find image URLs
                if (action === 'delete') {
                    for (const propertyId of propertyIds) {
                        try {
                            // Get property details to find image URLs
                            const getPropertyUrl = `${baseUrl}/crm.item.get?entityTypeId=${LISTINGS_ENTITY_TYPE_ID}&id=${propertyId}`;
                            const propertyResponse = await fetch(getPropertyUrl);
                            const propertyData = await propertyResponse.json();
                            console.log('Property data:', propertyData);
                            if (propertyData.result && propertyData.result.item) {
                                const property = propertyData.result.item;

                                // Delete images from S3
                                if (property.ufCrm15PhotoLinks && Array.isArray(property.ufCrm15PhotoLinks)) {
                                    for (const imageUrl of property.ufCrm15PhotoLinks) {
                                        try {
                                            await fetch('./delete-s3-object.php', {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                },
                                                body: JSON.stringify({
                                                    fileUrl: imageUrl
                                                })
                                            });
                                        } catch (error) {
                                            console.error(`Error deleting S3 object: ${imageUrl}`, error);
                                        }
                                    }
                                }

                                // Delete floorplan from S3 if exists
                                if (property.ufCrm15FloorPlan) {
                                    try {
                                        await fetch('./delete-s3-object.php', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                            },
                                            body: JSON.stringify({
                                                fileUrl: property.ufCrm15FloorPlan
                                            })
                                        });
                                    } catch (error) {
                                        console.error(`Error deleting S3 floorplan: ${property.ufCrm15FloorPlan}`, error);
                                    }
                                }

                                // Delete documents from S3
                                if (property.ufCrm15Documents && Array.isArray(property.ufCrm15Documents)) {
                                    for (const docUrl of property.ufCrm15Documents) {
                                        try {
                                            await fetch('./delete-s3-object.php', {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                },
                                                body: JSON.stringify({
                                                    fileUrl: docUrl
                                                })
                                            });
                                        } catch (error) {
                                            console.error(`Error deleting S3 document: ${docUrl}`, error);
                                        }
                                    }
                                }
                            }
                        } catch (error) {
                            console.error(`Error getting property details for deletion: ${propertyId}`, error);
                        }
                    }
                }

                const requests = propertyIds.map(propertyId => {
                    let url = `${apiUrl}&id=${propertyId}`;

                    if (action === 'publish') {
                        url += '&fields[ufCrm15Status]=PUBLISHED';

                        if (platformFieldMapping[platform]) {
                            url += `&fields[${platformFieldMapping[platform]}]=Y`;
                        } else {
                            url += `&fields[ufCrm15PfEnable]=Y&fields[ufCrm15BayutEnable]=Y&fields[ufCrm15DubizzleEnable]=Y&fields[ufCrm15WebsiteEnable]=Y`;
                        }
                    } else if (action === 'unpublish') {
                        if (platformFieldMapping[platform]) {
                            url += `&fields[${platformFieldMapping[platform]}]=N`;
                        } else {
                            url += `&fields[ufCrm15PfEnable]=N&fields[ufCrm15BayutEnable]=N&fields[ufCrm15DubizzleEnable]=N&fields[ufCrm15WebsiteEnable]=N&fields[ufCrm15Status]=UNPUBLISHED`;
                        }
                    } else if (action === 'archive') {
                        url += '&fields[ufCrm15Status]=ARCHIVED';
                    }

                    return fetch(url, {
                            method: 'GET'
                        })
                        .then(response => response.json())
                        .then(data => {})
                        .catch(error => {
                            console.error(`Error updating property ${propertyId}:`, error);
                        });
                });

                // Wait for all requests to finish
                await Promise.all(requests);

                location.reload();
            } catch (error) {
                console.error('Error handling bulk action:', error);
            }
        }
    }

    // Function to add watermark to the image
    function addWatermark(imageElement, watermarkImagePath) {
        return new Promise((resolve, reject) => {
            const watermarkImage = new Image();
            watermarkImage.src = watermarkImagePath;

            watermarkImage.onload = function() {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                const width = imageElement.width;
                const height = imageElement.height;

                canvas.width = width;
                canvas.height = height;

                // Draw the original image
                ctx.drawImage(imageElement, 0, 0, width, height);

                const watermarkAspect = watermarkImage.width / watermarkImage.height;
                const imageAspect = width / height;

                let watermarkWidth, watermarkHeight;

                if (watermarkAspect > imageAspect) {
                    watermarkWidth = width * 0.5;
                    watermarkHeight = watermarkWidth / watermarkAspect;
                } else {
                    watermarkHeight = height * 0.5;
                    watermarkWidth = watermarkHeight * watermarkAspect;
                }

                const xPosition = (width - watermarkWidth) / 2;
                const yPosition = (height - watermarkHeight) / 2;

                // Set opacity for the watermark
                ctx.globalAlpha = 0.9; // Opacity value between 0.0 (transparent) and 1.0 (opaque)
                ctx.drawImage(watermarkImage, xPosition, yPosition, watermarkWidth, watermarkHeight);

                // Reset alpha for any further drawings (if needed)
                ctx.globalAlpha = 1.0;

                const watermarkedImage = canvas.toDataURL('image/jpeg', 0.8);
                resolve(watermarkedImage);
            };

            watermarkImage.onerror = function() {
                reject('Failed to load watermark image.');
            };
        });
    }


    // Function to add watermark text to the image
    function addWatermarkText(imageElement, watermarkText) {
        return new Promise((resolve, reject) => {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const width = imageElement.width;
            const height = imageElement.height;

            canvas.width = width;
            canvas.height = height;

            ctx.drawImage(imageElement, 0, 0, width, height);

            // Set the watermark text properties
            ctx.font = '360px Arial'; // You can adjust the font size here
            ctx.fillStyle = 'rgba(255, 255, 255, 0.6)'; // White color with 50% transparency
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';

            // Add the watermark text to the image (centered)
            ctx.fillText(watermarkText, width / 2, height / 2);

            // Convert the image to JPEG with reduced quality (optional)
            const watermarkedImage = canvas.toDataURL('image/jpeg', 0.7); // Adjust quality as needed
            resolve(watermarkedImage);
        });
    }

    // Function to upload a file
    function uploadFile(file, isDocument = false) {
        const formData = new FormData();
        formData.append('file', file);

        if (isDocument) {
            formData.append('isDocument', 'true');
        }

        return fetch('upload-file.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.url) {
                    return data.url;
                } else {
                    console.error('Error uploading file (PHP backend):', data.error);
                    return null;
                }
            })
            .catch((error) => {
                console.error("Error uploading file:", error);
                return null;
            });
    }

    // Process base64 images
    async function processBase64Images(base64Images, watermarkPath, watermark = true) {
        const photoPaths = [];
        const TARGET_ASPECT_RATIO = 4 / 3;

        function resizeToAspectRatio(image) {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');

            let newWidth = image.width;
            let newHeight = image.height;
            const currentAspectRatio = image.width / image.height;

            if (currentAspectRatio > TARGET_ASPECT_RATIO) {
                newWidth = image.height * TARGET_ASPECT_RATIO;
                newHeight = image.height;
            } else if (currentAspectRatio < TARGET_ASPECT_RATIO) {
                newWidth = image.width;
                newHeight = image.width / TARGET_ASPECT_RATIO;
            }

            canvas.width = newWidth;
            canvas.height = newHeight;

            const xOffset = (newWidth - image.width) / 2;
            const yOffset = (newHeight - image.height) / 2;

            ctx.fillStyle = '#FFFFFF';
            ctx.fillRect(0, 0, newWidth, newHeight);

            ctx.drawImage(
                image,
                xOffset,
                yOffset,
                image.width,
                image.height
            );

            return canvas.toDataURL();
        }

        for (const base64Image of base64Images) {
            const regex = /^data:image\/(\w+);base64,/;
            const matches = base64Image.match(regex);

            if (matches) {
                const base64Data = base64Image.replace(regex, '');
                const imageData = atob(base64Data);

                const blob = new Blob([new Uint8Array(imageData.split('').map(c => c.charCodeAt(0)))], {
                    type: `image/${matches[1]}`,
                });
                const imageUrl = URL.createObjectURL(blob);

                const imageElement = new Image();
                imageElement.src = imageUrl;

                await new Promise((resolve, reject) => {
                    imageElement.onload = async () => {
                        try {
                            const resizedDataUrl = resizeToAspectRatio(imageElement);
                            const resizedImage = new Image();
                            resizedImage.src = resizedDataUrl;

                            await new Promise(resolve => {
                                resizedImage.onload = resolve;
                            });

                            const finalDataUrl = watermark ?
                                await addWatermark(resizedImage, watermarkPath) :
                                resizedDataUrl;

                            const finalBlob = dataURLToBlob(finalDataUrl);
                            const uploadedUrl = await uploadFile(finalBlob);

                            if (uploadedUrl) {
                                photoPaths.push(uploadedUrl);
                            } else {
                                console.error('Error uploading photo from base64 data');
                            }

                            resolve();
                        } catch (error) {
                            console.error('Error processing watermarking or uploading:', error);
                            reject(error);
                        } finally {
                            URL.revokeObjectURL(imageUrl);
                        }
                    };

                    imageElement.onerror = (error) => {
                        console.error('Failed to load image from URL:', error);
                        reject(error);
                    };
                });
            } else {
                console.error('Invalid base64 image data');
            }
        }

        return photoPaths;
    }

    function getAmenityName(amenityId) {
        const amenities = [{
                id: 'AC',
                label: 'Central air conditioning'
            },
            {
                id: 'AN',
                label: 'Cable-ready'
            },
            {
                id: 'AP',
                label: 'Near airport'
            },
            {
                id: 'BA',
                label: 'Balcony'
            },
            {
                id: 'BB',
                label: 'BBQ area'
            },
            {
                id: 'BC',
                label: 'Beach access'
            },
            {
                id: 'BH',
                label: 'Beach Access'
            },
            {
                id: 'BK',
                label: 'Kitchen Appliances'
            },
            {
                id: 'BL',
                label: 'View of Landmark'
            },
            {
                id: 'BT',
                label: 'Basement'
            },
            {
                id: 'BP',
                label: 'Basement parking'
            },
            {
                id: 'BW',
                label: 'Built in wardrobes'
            },
            {
                id: 'CA',
                label: 'Carpets'
            },
            {
                id: 'CL',
                label: 'Cleaning services'
            },
            {
                id: 'CR',
                label: 'Conference room'
            },
            {
                id: 'CS',
                label: 'Concierge Service'
            },
            {
                id: 'CV',
                label: 'Community view'
            },
            {
                id: 'DN',
                label: 'Pantry'
            },
            {
                id: 'DR',
                label: 'Drivers room'
            },
            {
                id: 'EO',
                label: 'East orientation'
            },
            {
                id: 'FF',
                label: 'Fully fitted kitchen'
            },
            {
                id: 'GA',
                label: 'Private garage'
            },
            {
                id: 'GF',
                label: 'Ground floor'
            },
            {
                id: 'GR',
                label: 'Garden view'
            },
            {
                id: 'GZ',
                label: 'Gazebo'
            },
            {
                id: 'HO',
                label: 'Near hospital'
            },
            {
                id: 'HT',
                label: 'Heating'
            },
            {
                id: 'IC',
                label: 'Within a Compound'
            },
            {
                id: 'IS',
                label: 'Indoor swimming pool'
            },
            {
                id: 'LF',
                label: 'On low floor'
            },
            {
                id: 'MB',
                label: 'Marble floors'
            },
            {
                id: 'MF',
                label: 'On mid floor'
            },
            {
                id: 'MR',
                label: 'Maids Room'
            },
            {
                id: 'MO',
                label: 'Near metro'
            },
            {
                id: 'MT',
                label: 'Maintenance'
            },
            {
                id: 'MS',
                label: 'Maid Service'
            },
            {
                id: 'NM',
                label: 'Near mosque'
            },
            {
                id: 'NO',
                label: 'North orientation'
            },
            {
                id: 'NS',
                label: 'Near school'
            },
            {
                id: 'PA',
                label: 'Pets allowed'
            },
            {
                id: 'PG',
                label: 'Garden'
            },
            {
                id: 'PK',
                label: 'Public parks'
            },
            {
                id: 'PL',
                label: 'Private Land'
            },
            {
                id: 'PP',
                label: 'Swimming pool'
            },
            {
                id: 'PR',
                label: 'Children Play Area'
            },
            {
                id: 'PY',
                label: 'Private Gym'
            },
            {
                id: 'RA',
                label: 'Reception area'
            },
            {
                id: 'RT',
                label: 'Near restaurants'
            },
            {
                id: 'SA',
                label: 'Sauna'
            },
            {
                id: 'SG',
                label: 'Storage room'
            },
            {
                id: 'SH',
                label: 'Core and Shell'
            },
            {
                id: 'SR',
                label: 'Steam room'
            },
            {
                id: 'SS',
                label: 'Spa'
            },
            {
                id: 'ST',
                label: 'Study'
            },
            {
                id: 'SY',
                label: 'Shared Gym'
            },
            {
                id: 'SP',
                label: 'Shared swimming pool'
            },
            {
                id: 'SV',
                label: 'Server room'
            },
            {
                id: 'TR',
                label: 'Terrace'
            },
            {
                id: 'UI',
                label: 'Upgraded interior'
            },
            {
                id: 'VF',
                label: 'Visitor Parking'
            },
            {
                id: 'VW',
                label: 'Sea/Water view'
            },
            {
                id: 'WC',
                label: 'Walk-in Closet'
            },
            {
                id: 'WO',
                label: 'West orientation'
            },
            {
                id: 'VP',
                label: 'Visitors parking'
            },
            {
                id: 'VT',
                label: 'Near veterinary'
            },
            {
                id: 'VW',
                label: 'View of Water'
            },
            {
                id: "SE",
                label: "Security"
            },
            {
                id: "CO",
                label: "Children's Pool"
            },
        ];

        return amenities.find(amenity => amenity.id === amenityId)?.label || amenityId;
    }

    function getAmenityId(amenityName) {
        const amenities = [{
                id: 'AC',
                label: 'Central air conditioning'
            },
            {
                id: 'AN',
                label: 'Cable-ready'
            },
            {
                id: 'AP',
                label: 'Near airport'
            },
            {
                id: 'BA',
                label: 'Balcony'
            },
            {
                id: 'BB',
                label: 'BBQ area'
            },
            {
                id: 'BC',
                label: 'Beach access'
            },
            {
                id: 'BH',
                label: 'Beach Access'
            },
            {
                id: 'BK',
                label: 'Kitchen Appliances'
            },
            {
                id: 'BL',
                label: 'View of Landmark'
            },
            {
                id: 'BT',
                label: 'Basement'
            },
            {
                id: 'BP',
                label: 'Basement parking'
            },
            {
                id: 'BW',
                label: 'Built in wardrobes'
            },
            {
                id: 'CA',
                label: 'Carpets'
            },
            {
                id: 'CL',
                label: 'Cleaning services'
            },
            {
                id: 'CR',
                label: 'Conference room'
            },
            {
                id: 'CS',
                label: 'Concierge Service'
            },
            {
                id: 'CV',
                label: 'Community view'
            },
            {
                id: 'DN',
                label: 'Pantry'
            },
            {
                id: 'DR',
                label: 'Drivers room'
            },
            {
                id: 'EO',
                label: 'East orientation'
            },
            {
                id: 'FF',
                label: 'Fully fitted kitchen'
            },
            {
                id: 'GA',
                label: 'Private garage'
            },
            {
                id: 'GF',
                label: 'Ground floor'
            },
            {
                id: 'GR',
                label: 'Garden view'
            },
            {
                id: 'GZ',
                label: 'Gazebo'
            },
            {
                id: 'HO',
                label: 'Near hospital'
            },
            {
                id: 'HT',
                label: 'Heating'
            },
            {
                id: 'IC',
                label: 'Within a Compound'
            },
            {
                id: 'IS',
                label: 'Indoor swimming pool'
            },
            {
                id: 'LF',
                label: 'On low floor'
            },
            {
                id: 'MB',
                label: 'Marble floors'
            },
            {
                id: 'MF',
                label: 'On mid floor'
            },
            {
                id: 'MR',
                label: 'Maids Room'
            },
            {
                id: 'MO',
                label: 'Near metro'
            },
            {
                id: 'MT',
                label: 'Maintenance'
            },
            {
                id: 'MS',
                label: 'Maid Service'
            },
            {
                id: 'NM',
                label: 'Near mosque'
            },
            {
                id: 'NO',
                label: 'North orientation'
            },
            {
                id: 'NS',
                label: 'Near school'
            },
            {
                id: 'PA',
                label: 'Pets allowed'
            },
            {
                id: 'PG',
                label: 'Garden'
            },
            {
                id: 'PK',
                label: 'Public parks'
            },
            {
                id: 'PL',
                label: 'Private Land'
            },
            {
                id: 'PP',
                label: 'Swimming pool'
            },
            {
                id: 'PR',
                label: 'Children Play Area'
            },
            {
                id: 'PY',
                label: 'Private Gym'
            },
            {
                id: 'RA',
                label: 'Reception area'
            },
            {
                id: 'RT',
                label: 'Near restaurants'
            },
            {
                id: 'SA',
                label: 'Sauna'
            },
            {
                id: 'SG',
                label: 'Storage room'
            },
            {
                id: 'SH',
                label: 'Core and Shell'
            },
            {
                id: 'SR',
                label: 'Steam room'
            },
            {
                id: 'SS',
                label: 'Spa'
            },
            {
                id: 'ST',
                label: 'Study'
            },
            {
                id: 'SY',
                label: 'Shared Gym'
            },
            {
                id: 'SP',
                label: 'Shared swimming pool'
            },
            {
                id: 'SV',
                label: 'Server room'
            },
            {
                id: 'TR',
                label: 'Terrace'
            },
            {
                id: 'UI',
                label: 'Upgraded interior'
            },
            {
                id: 'VF',
                label: 'Visitor Parking'
            },
            {
                id: 'VW',
                label: 'Sea/Water view'
            },
            {
                id: 'WC',
                label: 'Walk-in Closet'
            },
            {
                id: 'WO',
                label: 'West orientation'
            },
            {
                id: 'VP',
                label: 'Visitors parking'
            },
            {
                id: 'VT',
                label: 'Near veterinary'
            },
            {
                id: 'VW',
                label: 'View of Water'
            },
            {
                id: "SE",
                label: "Security"
            },
            {
                id: "CO",
                label: "Children's Pool"
            },
        ];

        return amenities.find(amenity => amenity.label === amenityName)?.id || amenityName;
    }

    // Function to convert data URL to Blob
    function dataURLToBlob(dataURL) {
        const byteString = atob(dataURL.split(',')[1]);
        const arrayBuffer = new ArrayBuffer(byteString.length);
        const uintArray = new Uint8Array(arrayBuffer);
        for (let i = 0; i < byteString.length; i++) {
            uintArray[i] = byteString.charCodeAt(i);
        }
        return new Blob([uintArray], {
            type: 'image/png'
        });
    }

    // Function to fetch a property
    async function fetchProperty(id) {
        const url = `${API_BASE_URL}crm.item.get?entityTypeId=${LISTINGS_ENTITY_TYPE_ID}&id=${id}`;
        console.log("url: ", url)
        const response = await fetch(url);
        const data = await response.json();
        console.log("data: ", data)
        console.log("data.result: ", data.result)
        console.log("data.result.item: ", data.result.item);

        if (data.result && data.result.item) {
            const property = data.result.item;

            // Management
            document.getElementById('reference').value = property.ufCrm15ReferenceNumber;

            // Landlord 1
            document.getElementById('landlord_name').value = property.ufCrm15LandlordName;
            document.getElementById('landlord_email').value = property.ufCrm15LandlordEmail;
            document.getElementById('landlord_phone').value = property.ufCrm15LandlordContact;

            Array.from(document.getElementById('availability').options).forEach(option => {
                if (option.value == property.ufCrm15Availability) option.selected = true;
            });
            document.getElementById('available_from').value = formatInputDate(property.ufCrm15AvailableFrom);
            document.getElementById('contract_expiry').value = formatInputDate(property.ufCrm15ContractExpiryDate);

            // Specifications
            document.getElementById('title_deed').value = property.ufCrm15TitleDeed;
            document.getElementById('size').value = property.ufCrm15Size;
            document.getElementById('unit_no').value = property.ufCrm15UnitNo;
            document.getElementById('bathrooms').value = property.ufCrm15Bathroom;
            document.getElementById('parkings').value = property.ufCrm15Parking;
            document.getElementById('total_plot_size').value = property.ufCrm15TotalPlotSize;
            document.getElementById('lot_size').value = property.ufCrm15LotSize;
            document.getElementById('buildup_area').value = property.ufCrm15BuildupArea;
            document.getElementById('layout_type').value = property.ufCrm15LayoutType;
            document.getElementById('project_name').value = property.ufCrm15ProjectName;
            document.getElementById('build_year').value = property.ufCrm15BuildYear;
            Array.from(document.getElementById('property_type').options).forEach(option => {
                if (option.value === property.ufCrm15PropertyType) option.selected = true;
            });
            Array.from(document.getElementById('offering_type').options).forEach(option => {
                if (option.value === property.ufCrm15OfferingType) option.selected = true;
            });
            Array.from(document.getElementById('bedrooms').options).forEach(option => {
                if (option.value == property.ufCrm15Bedroom) option.selected = true;
            });
            Array.from(document.getElementById('furnished').options).forEach(option => {
                if (option.value == property.ufCrm15Furnished) option.selected = true;
            });
            Array.from(document.getElementById('project_status').options).forEach(option => {
                if (option.value == property.ufCrm15ProjectStatus) option.selected = true;
            });
            Array.from(document.getElementById('sale_type').options).forEach(option => {
                if (option.value == property.ufCrm15SaleType) option.selected = true;
            });
            Array.from(document.getElementById('ownership').options).forEach(option => {
                if (option.value == property.ufCrm15Ownership) option.selected = true;
            });

            // Property Permit
            document.getElementById('rera_permit_number').value = property.ufCrm15ReraPermitNumber
            document.getElementById('dtcm_permit_number').value = property.ufCrm15DtcmPermitNumber
            document.getElementById('rera_issue_date').value = formatInputDate(property.ufCrm15ReraPermitIssueDate);
            document.getElementById('rera_expiration_date').value = formatInputDate(property.ufCrm15ReraPermitExpirationDate);

            // Pricing
            document.getElementById('price').value = property.ufCrm15Price;
            document.getElementById('payment_method').value = property.ufCrm15PaymentMethod;
            document.getElementById('downpayment_price').value = property.ufCrm15DownPaymentPrice;
            document.getElementById('service_charge').value = property.ufCrm15ServiceCharge;
            property.ufCrm15HidePrice == "Y" ? document.getElementById('hide_price').checked = true : document.getElementById('hide_price').checked = false;
            Array.from(document.getElementById('rental_period').options).forEach(option => {
                if (option.value == property.ufCrm15RentalPeriod) option.selected = true;
            });
            Array.from(document.getElementById('cheques').options).forEach(option => {
                if (option.value == property.ufCrm15NoOfCheques) option.selected = true;
            });
            Array.from(document.getElementById('financial_status').options).forEach(option => {
                if (option.value == property.ufCrm15FinancialStatus) option.selected = true;
            });

            // Title and Description
            document.getElementById('title_en').value = property.ufCrm15TitleEn;
            document.getElementById('description_en').textContent = property.ufCrm15DescriptionEn;
            document.getElementById('title_ar').value = property.ufCrm15TitleAr;
            document.getElementById('description_ar').textContent = property.ufCrm15DescriptionAr;
            document.getElementById('brochure_description_1').textContent = property.ufCrm15BrochureDescription;
            document.getElementById('brochure_description_2').textContent = property.ufCrm_15_BROCHURE_DESCRIPTION_2;

            document.getElementById('titleEnCount').textContent = document.getElementById('title_en').value.length;
            document.getElementById('descriptionEnCount').textContent = document.getElementById('description_en').textContent.length;
            document.getElementById('titleArCount').textContent = document.getElementById('title_ar').value.length;
            document.getElementById('descriptionArCount').textContent = document.getElementById('description_ar').textContent.length;
            document.getElementById('brochureDescription1Count').textContent = document.getElementById('brochure_description_1').textContent.length;
            document.getElementById('brochureDescription2Count').textContent = document.getElementById('brochure_description_2').textContent.length;

            // Location
            document.getElementById('pf_location').value = property.ufCrm15Location;
            document.getElementById('pf_city').value = property.ufCrm15City;
            document.getElementById('pf_community').value = property.ufCrm15Community;
            document.getElementById('pf_subcommunity').value = property.ufCrm15SubCommunity;
            document.getElementById('pf_building').value = property.ufCrm15Tower;
            document.getElementById('bayut_location').value = property.ufCrm15BayutLocation;
            document.getElementById('bayut_city').value = property.ufCrm15BayutCity;
            document.getElementById('bayut_community').value = property.ufCrm15BayutCommunity;
            document.getElementById('bayut_subcommunity').value = property.ufCrm15BayutSubCommunity;
            document.getElementById('bayut_building').value = property.ufCrm15BayutTower;

            if (property.ufCrm15Geopoints) {
                const [latitude, longitude] = property.ufCrm15Geopoints.split(',').map(coord => coord.trim());
                document.getElementById('latitude').value = latitude;
                document.getElementById('longitude').value = longitude;
            }

            // Photos and Videos
            document.getElementById('video_tour_url').value = property.ufCrm15VideoTourUrl;
            document.getElementById('360_view_url').value = property.ufCrm_15_360_VIEW_URL;
            document.getElementById('qr_code_url').value = property.ufCrm15QrCodePropertyBooster;
            // Photos
            // Floor Plan

            // Portals
            property.ufCrm15PfEnable == "Y" ? document.getElementById('pf_enable').checked = true : document.getElementById('pf_enable').checked = false;
            property.ufCrm15BayutEnable == "Y" ? document.getElementById('bayut_enable').checked = true : document.getElementById('bayut_enable').checked = false;
            property.ufCrm15DubizzleEnable == "Y" ? document.getElementById('dubizzle_enable').checked = true : document.getElementById('dubizzle_enable').checked = false;
            property.ufCrm15WebsiteEnable == "Y" ? document.getElementById('website_enable').checked = true : document.getElementById('website_enable').checked = false;
            property.ufCrm15Watermark == "Y" ? document.getElementById('watermark').checked = true : document.getElementById('watermark').checked = false;
            if (document.getElementById('dubizzle_enable').checked && document.getElementById('bayut_enable').value) {
                toggle_bayut_dubizzle.checked = true;
            }

            switch (property.ufCrm15Status) {
                case 'PUBLISHED':
                    document.getElementById('publish').checked = true;
                    break;
                case 'UNPUBLISHED':
                    document.getElementById('unpublish').checked = true;
                    break;
                case 'LIVE':
                    document.getElementById('live').checked = true;
                    break;
                case 'DRAFT':
                    document.getElementById('draft').checked = true;
                    break;
                case 'ARCHIVED':
                    document.getElementById('archive').checked = true;
                    break;
                case 'POCKET':
                    document.getElementById('pocket').checked = true;
                    break;
            }

            function ensureOptionExistsAndSelect(selectElementId, value, label) {
                const selectElement = document.getElementById(selectElementId);
                const existingOption = document.querySelector(`#${selectElementId} option[value="${value}"]`);

                if (!existingOption) {
                    const newOption = document.createElement('option');
                    newOption.value = value;
                    newOption.textContent = label || 'Unknown Option';
                    newOption.selected = true;
                    selectElement.appendChild(newOption);
                } else {
                    existingOption.selected = true;
                }
            }

            ensureOptionExistsAndSelect('listing_agent', property.ufCrm15AgentId, property.ufCrm15AgentName);
            ensureOptionExistsAndSelect('listing_owner', property.ufCrm15ListingOwner, property.ufCrm15ListingOwner);
            ensureOptionExistsAndSelect('developer', property.ufCrm15Developers, property.ufCrm15Developers);

            // Notes
            function addExistingNote(note) {
                const li = document.createElement("li");
                li.classList.add("text-gray-700", "p-2", "flex", "justify-between", "items-center", "mb-2", "bg-gray-100", "rounded-md");

                li.innerHTML = `
                    ${note} 
                    <button class="text-red-500 hover:text-red-700" onclick="removeNote(this)">×</button>
                `;

                document.getElementById("notesList").appendChild(li);
                updateNotesInput();
            }

            if (property.ufCrm15Notes.length > 0) {
                property.ufCrm15Notes.forEach(note => {
                    addExistingNote(note);
                });
            }

            // Amenities
            function addExistingAmenity(amenity) {
                if (!selectedAmenities.some(a => a.id === amenity)) {
                    selectedAmenities.push({
                        id: amenity,
                        label: getAmenityName(amenity)
                    });
                }

                const li = document.createElement("li");
                li.classList.add("text-gray-700", "p-2", "flex", "justify-between", "items-center", "mb-2", "bg-gray-100", "rounded-md");

                li.innerHTML = `
                    ${getAmenityName(amenity)} 
                    <button type="button" class="text-red-500 hover:text-red-700" onclick="removeAmenity('${amenity}')">×</button>
                `;

                document.getElementById("selectedAmenities").appendChild(li);
                updateAmenitiesInput();
            }

            if (property.ufCrm15Amenities && property.ufCrm15Amenities.length > 0) {
                property.ufCrm15Amenities.forEach(amenity => {
                    addExistingAmenity(amenity);
                });
            }


            return property;

        } else {
            console.error('Invalid property data:', data);
            document.getElementById('property-details').textContent = 'Failed to load property details.';
        }
    }

    // Function to check if any property is selected
    function isPropertySelected() {
        var checkboxes = document.querySelectorAll('input[name="property_ids[]"]:checked');
        var propertyIds = Array.from(checkboxes).map(checkbox => checkbox.value);

        return propertyIds && propertyIds.length > 0;
    }

    // Function to select and add properties to agent transfer form
    function selectAndAddPropertiesToAgentTransfer() {
        var checkboxes = document.querySelectorAll('input[name="property_ids[]"]:checked');
        var propertyIds = Array.from(checkboxes).map(checkbox => checkbox.value);

        if (!isPropertySelected()) {
            return alert('Please select at least one property.');
        }

        if (document.getElementById('transferAgentPropertyIds')) {
            document.getElementById('transferAgentPropertyIds').value = propertyIds.join(',');
        } else {
            localStorage.removeItem('transferAgentPropertyIds');
            localStorage.setItem('transferAgentPropertyIds', propertyIds.join(','));
        }

        const agentModal = new bootstrap.Modal(document.getElementById('transferAgentModal'));
        agentModal.show();
    }

    // Function to select and add properties to owner transfer form
    function selectAndAddPropertiesToOwnerTransfer() {
        var checkboxes = document.querySelectorAll('input[name="property_ids[]"]:checked');
        var propertyIds = Array.from(checkboxes).map(checkbox => checkbox.value);

        if (!isPropertySelected()) {
            return alert('Please select at least one property.');
        }

        if (document.getElementById('transferOwnerPropertyIds')) {
            document.getElementById('transferOwnerPropertyIds').value = propertyIds.join(',');
        } else {
            localStorage.removeItem('transferOwnerPropertyIds');
            localStorage.setItem('transferOwnerPropertyIds', propertyIds.join(','));
        }


        const ownerModal = new bootstrap.Modal(document.getElementById('transferOwnerModal'));
        ownerModal.show();
    }

    // Function to calculate square meters
    function sqftToSqm(sqft) {
        const sqm = sqft * 0.092903;
        return parseFloat(sqm.toFixed(2));
    }
</script>

</body>

</html>