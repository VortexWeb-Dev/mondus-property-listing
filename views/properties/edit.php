<div class="w-4/5 mx-auto py-8">
    <div class="flex justify-between items-center mb-6">
        <form class="w-full space-y-4" id="editPropertyForm" onsubmit="handleEditProperty(event)">
            <!-- Management -->
            <?php include_once('views/components/add-property/management.php'); ?>
            <!-- Specifications -->
            <?php include_once('views/components/add-property/specifications.php'); ?>
            <!-- Property Permit -->
            <?php include_once('views/components/add-property/permit.php'); ?>
            <!-- Pricing -->
            <?php include_once('views/components/add-property/pricing.php'); ?>
            <!-- Title and Description -->
            <?php include_once('views/components/add-property/title.php'); ?>
            <!-- Amenities -->
            <?php include_once('views/components/add-property/amenities.php'); ?>
            <!-- Location -->
            <?php include_once('views/components/add-property/location.php'); ?>
            <!-- Photos and Videos -->
            <?php include_once('views/components/add-property/media.php'); ?>
            <!-- Floor Plan -->
            <?php include_once('views/components/add-property/floorplan.php'); ?>
            <!-- Documents -->
            <?php // include_once('views/components/add-property/documents.php'); 
            ?>
            <!-- Notes -->
            <?php include_once('views/components/add-property/notes.php'); ?>
            <!-- Portals -->
            <?php include_once('views/components/add-property/portals.php'); ?>
            <!-- Status -->
            <?php include_once('views/components/add-property/status.php'); ?>

            <div class="mt-6 flex justify-end space-x-4">
                <button type="button" onclick="javascript:history.back()" class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-1">
                    Back
                </button>
                <button type="submit" id="submitButton" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1">
                    Submit
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById("offering_type").addEventListener("change", function() {
        const offeringType = this.value;
        console.log(offeringType);

        if (offeringType == 'RR' || offeringType == 'CR') {
            document.getElementById("rental_period").setAttribute("required", true);
            document.querySelector('label[for="rental_period"]').innerHTML = 'Rental Period (if rental) <span class="text-danger">*</span>';
        } else {
            document.getElementById("rental_period").removeAttribute("required");
            document.querySelector('label[for="rental_period"]').innerHTML = 'Rental Period (if rental)';
        }
    })

    async function updateItem(entityTypeId, fields, id) {
        try {
            const response = await fetch(`${API_BASE_URL}crm.item.update?entityTypeId=${entityTypeId}&id=${id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    fields,
                }),
            });

            if (response.ok) {
                window.location.href = 'index.php?page=properties';
            } else {
                console.error('Failed to add item');
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    async function handleEditProperty(e) {
        e.preventDefault();

        const submitButton = document.getElementById('submitButton');
        submitButton.disabled = true;
        submitButton.innerHTML = 'Updating...';

        const form = document.getElementById('editPropertyForm');
        const formData = new FormData(form);
        const data = {};

        formData.forEach((value, key) => {
            data[key] = typeof value === 'string' ? value.trim() : value;
        });

        const agent = await getAgent(data.listing_agent);

        const fields = {
            "ufCrm15TitleDeed": data.title_deed,
            "ufCrm15ReferenceNumber": data.reference,
            "ufCrm15OfferingType": data.offering_type,
            "ufCrm15PropertyType": data.property_type,
            "ufCrm15Price": data.price,
            "ufCrm15TitleEn": data.title_en,
            "ufCrm15DescriptionEn": data.description_en,
            "ufCrm15TitleAr": data.title_ar,
            "ufCrm15DescriptionAr": data.description_ar,
            "ufCrm15Size": data.size,
            "ufCrm15Bedroom": data.bedrooms,
            "ufCrm15Bathroom": data.bathrooms,
            "ufCrm15Parking": data.parkings,
            "ufCrm15Geopoints": `${data.latitude}, ${data.longitude}`,
            "ufCrm15PermitNumber": data.dtcm_permit_number,
            "ufCrm15RentalPeriod": data.rental_period,
            "ufCrm15City": data.bayut_city,
            "ufCrm15Community": data.bayut_community,
            "ufCrm15SubCommunity": data.bayut_subcommunity,
            "ufCrm15Furnished": data.furnished,
            "ufCrm15TotalPlotSize": data.total_plot_size,
            "ufCrm15LotSize": data.lot_size,
            "ufCrm15BuildupArea": data.buildup_area,
            "ufCrm15LayoutType": data.layout_type,
            "ufCrm15ProjectName": data.project_name,
            "ufCrm15ProjectStatus": data.project_status,
            "ufCrm15Ownership": data.ownership,
            "ufCrm15Developers": data.developer,
            "ufCrm15BuildYear": data.build_year,
            "ufCrm15Availability": data.availability,
            "ufCrm15AvailableFrom": data.available_from,
            "ufCrm15PaymentMethod": data.payment_method,
            "ufCrm15DownPaymentPrice": data.downpayment_price,
            "ufCrm15NoOfCheques": data.cheques,
            "ufCrm15ServiceCharge": data.service_charge,
            "ufCrm15FinancialStatus": data.financial_status,
            "ufCrm15VideoTourUrl": data.video_tour_url,
            "ufCrm_15_360_VIEW_URL": data["360_view_url"],
            "ufCrm15QrCodePropertyBooster": data.qr_code_url,

            "ufCrm15Location": data.pf_location,
            "ufCrm15City": data.pf_city,
            "ufCrm15Community": data.pf_community,
            "ufCrm15SubCommunity": data.pf_subcommunity,
            "ufCrm15Tower": data.pf_building,
            "ufCrm15BayutLocation": data.bayut_location,
            "ufCrm15BayutCity": data.bayut_city,
            "ufCrm15BayutCommunity": data.bayut_community,
            "ufCrm15BayutSubCommunity": data.bayut_subcommunity,
            "ufCrm15BayutTower": data.bayut_building,

            "ufCrm15Status": data.status,
            "ufCrm15ReraPermitNumber": data.rera_permit_number,
            "ufCrm15ReraPermitIssueDate": data.rera_issue_date,
            "ufCrm15ReraPermitExpirationDate": data.rera_expiration_date,
            "ufCrm15DtcmPermitNumber": data.dtcm_permit_number,
            "ufCrm15ListingOwner": data.listing_owner,
            // Landlord 1
            "ufCrm15LandlordName": data.landlord_name,
            "ufCrm15LandlordEmail": data.landlord_email,
            "ufCrm15LandlordContact": data.landlord_phone,

            "ufCrm15ContractExpiryDate": data.contract_expiry,
            "ufCrm15UnitNo": data.unit_no,
            "ufCrm15SaleType": data.sale_type,
            "ufCrm15BrochureDescription": data.brochure_description_1,
            "ufCrm_15_BROCHURE_DESCRIPTION_2": data.brochure_description_2,

            "ufCrm15HidePrice": data.hide_price === "on" ? "Y" : "N",
            "ufCrm15PfEnable": data.pf_enable === "on" ? "Y" : "N",
            "ufCrm15BayutEnable": data.bayut_enable === "on" ? "Y" : "N",
            "ufCrm15DubizzleEnable": data.dubizzle_enable === "on" ? "Y" : "N",
            "ufCrm15WebsiteEnable": data.website_enable === "on" ? "Y" : "N",
            "ufCrm15Watermark": data.watermark === "on" ? "Y" : "N",
        };

        if (agent) {
            Object.assign(fields, {
                "ufCrm15AgentId": agent.ufCrm14AgentId,
                "ufCrm15AgentName": agent.ufCrm14AgentName,
                "ufCrm15AgentEmail": agent.ufCrm14AgentEmail,
                "ufCrm15AgentPhone": agent.ufCrm14AgentMobile,
                "ufCrm15AgentPhoto": agent.ufCrm14AgentPhoto,
                "ufCrm15AgentLicense": agent.ufCrm14AgentLicense,
            });
        }

        const notesString = data.notes;
        if (notesString) {
            const notesArray = JSON.parse(notesString);
            fields["ufCrm15Notes"] = notesArray;
        }

        const amenitiesString = data.amenities;
        if (amenitiesString) {
            const amenitiesArray = JSON.parse(amenitiesString);
            fields["ufCrm15Amenities"] = amenitiesArray;
        }

        const photos = document.getElementById('selectedImages').value;
        const existingPhotosString = document.getElementById('existingPhotos').value;

        if (existingPhotosString) {
            const existingPhotos = JSON.parse(existingPhotosString) || [];

            if (photos.length > 0) {
                const fixedPhotos = photos.replace(/\\'/g, '"');
                const photoArray = JSON.parse(fixedPhotos);
                const watermarkPath = 'assets/images/watermark.png?cache=' + Date.now();
                const uploadedImages = await processBase64Images(photoArray, watermarkPath);

                fields["ufCrm15PhotoLinks"] = uploadedImages.length > 0 ? [...existingPhotos, ...uploadedImages] : [...existingPhotos];
            } else {
                fields["ufCrm15PhotoLinks"] = [...existingPhotos];
            }
        }

        const floorplans = document.getElementById('selectedFloorplan').value;
        const existingFloorplansString = document.getElementById('existingFloorplan').value;

        if (existingFloorplansString || floorplans.length > 0) {
            const existingFloorplans = JSON.parse(existingFloorplansString || "[]");

            if (floorplans.length > 0) {

                const fixedFloorplans = floorplans.replace(/\\'/g, '"');
                const floorplanArray = JSON.parse(fixedFloorplans);
                const watermarkPath = 'assets/images/watermark.png?cache=' + Date.now();
                const uploadedFloorplans = await processBase64Images(floorplanArray, watermarkPath);


                fields["ufCrm15FloorPlan"] = uploadedFloorplans.length > 0 ?
                    uploadedFloorplans[0] :
                    existingFloorplans[0] || null;
            } else {

                fields["ufCrm15FloorPlan"] = existingFloorplans[0] || null;
            }
        } else {

            fields["ufCrm15FloorPlan"] = null;
        }



        updateItem(LISTINGS_ENTITY_TYPE_ID, fields, <?php echo $_GET['id']; ?>);
    }

    document.addEventListener('DOMContentLoaded', async () => {
        const property = await fetchProperty(<?php echo $_GET['id']; ?>);

        const containers = [{
                type: "photos",
                newLinks: [],
                selectedFiles: [],
                existingLinks: property['ufCrm15PhotoLinks'] || [],
                newPreviewContainer: document.getElementById('newPhotoPreviewContainer'),
                existingPreviewContainer: document.getElementById('existingPhotoPreviewContainer'),
                selectedInput: document.getElementById('selectedImages'),
                existingInput: document.getElementById('existingPhotos'),
            },
            {
                type: "floorplan",
                newLinks: [],
                selectedFiles: [],
                existingLinks: property['ufCrm15FloorPlan'] ? [property['ufCrm15FloorPlan']] : [],
                newPreviewContainer: document.getElementById('newFloorplanPreviewContainer'),
                existingPreviewContainer: document.getElementById('existingFloorplanPreviewContainer'),
                selectedInput: document.getElementById('selectedFloorplan'),
                existingInput: document.getElementById('existingFloorplan'),
            },
        ];

        containers.forEach((container) => {
            initializeContainer(container);
        });

        function initializeContainer(container) {
            // Add Swapy only if slots exist
            function addSwapy(previewContainer) {
                console.log('swappy');

                const slots = previewContainer.querySelectorAll('[data-swapy-slot]');
                if (slots.length === 0) {
                    console.warn(`No slots found in preview container:`, previewContainer);
                    return; // Skip Swapy initialization if no slots are present
                }

                const swapy = Swapy.createSwapy(previewContainer, {
                    animation: 'dynamic',
                    swapMode: 'drop',
                });

                swapy.onSwapEnd((event) => {
                    if (event.hasChanged) {
                        console.log('Swap end event:', event);

                        const updatedImageLinks = [];
                        event.slotItemMap.asMap.forEach((item) => {
                            const element = document.querySelector(`[data-swapy-item="${item}"]`);
                            updatedImageLinks.push(element.querySelector('img').src);
                        });

                        if (previewContainer === container.newPreviewContainer) {
                            container.newLinks = updatedImageLinks;
                            previewImages(container.newLinks, container.newPreviewContainer);
                        } else {
                            container.existingLinks = updatedImageLinks;
                            previewImages(container.existingLinks, container.existingPreviewContainer);
                        }
                    }
                });
            }

            // Update photo preview for selected files
            function updatePhotoPreview() {
                const promises = container.selectedFiles.map((file) => {
                    return new Promise((resolve) => {
                        const reader = new FileReader();
                        reader.readAsDataURL(file);
                        reader.onload = function(e) {
                            container.newLinks.push(e.target.result);
                            resolve();
                        };
                    });
                });

                Promise.all(promises).then(() => {
                    previewImages(container.newLinks, container.newPreviewContainer);
                });
            }

            // Render images into the preview container
            function previewImages(imageLinks, previewContainer) {
                console.log('previewImages');

                previewContainer.innerHTML = '';

                if (imageLinks.length === 0) {
                    previewContainer.innerHTML = '<p class="text-muted">No images to display.</p>';
                    updateSelectedImagesInput();
                    return; // Exit if no images to preview
                }

                let row = document.createElement('div');
                row.classList.add('shuffle-row');

                imageLinks.forEach((imageSrc, i) => {
                    if (i % 3 === 0 && i !== 0) {
                        previewContainer.appendChild(row);
                        row = document.createElement('div');
                        row.classList.add('shuffle-row');
                    }

                    const slot = document.createElement('div');
                    slot.classList.add('slot');
                    slot.setAttribute('data-swapy-slot', i + 1);

                    const item = document.createElement('div');
                    item.classList.add('item');
                    item.setAttribute('data-swapy-item', String.fromCharCode(97 + i));

                    const image = document.createElement('div');
                    const img = document.createElement('img');
                    img.src = imageSrc;

                    image.appendChild(img);
                    item.appendChild(image);
                    slot.appendChild(item);

                    const removeBtn = document.createElement('button');
                    removeBtn.innerHTML = "&times;";
                    removeBtn.classList.add(
                        "position-absolute",
                        "top-0",
                        "end-0",
                        "btn",
                        "btn-sm",
                        "btn-danger",
                        "m-1"
                    );
                    removeBtn.style.zIndex = "1";

                    removeBtn.addEventListener('click', function(event) {
                        // event.preventDefault();
                        event.stopImmediatePropagation();
                        console.log("removeBtn.onclick", i);

                        if (previewContainer === container.newPreviewContainer) {
                            container.newLinks.splice(i, 1);
                            previewImages(container.newLinks, container.newPreviewContainer);
                        } else {
                            container.existingLinks.splice(i, 1);
                            previewImages(container.existingLinks, container.existingPreviewContainer);
                        }
                    });

                    item.appendChild(removeBtn);
                    row.appendChild(slot);
                });

                previewContainer.appendChild(row);
                addSwapy(previewContainer);
                updateSelectedImagesInput();
            }

            // Update hidden input values for the selected and existing images
            function updateSelectedImagesInput() {
                console.log("updateSelectedImagesInput");

                container.selectedInput.value = JSON.stringify(container.newLinks);
                container.existingInput.value = JSON.stringify(container.existingLinks);
            }

            // Handle file selection
            document.getElementById(container.type).addEventListener('change', function(event) {
                const files = Array.from(event.target.files);
                container.selectedFiles = [];

                files.forEach((file) => {
                    if (file.size >= 10 * 1024 * 1024) {
                        // alert(`The file "${file.name}" is too large (10MB or greater). Please select a smaller file.`);
                        document.getElementById(`${container.type}Message`).classList.remove('hidden');
                        document.getElementById(`${constainer.type}Message`).textContent = `The file "${file.name}" is too large (10MB or greater). Please select a smaller file.`;
                    } else if (!container.selectedFiles.some((f) => f.name === file.name)) {
                        container.selectedFiles.push(file);
                        document.getElementById(`${container.type}Message`).classList.add('hidden');
                    }
                });

                updatePhotoPreview();
            });

            // Initialize preview with existing links
            previewImages(container.existingLinks, container.existingPreviewContainer);
        }
    });
</script>