<?php include 'views/components/index-buttons.php'; ?>

<div class="w-4/5 mx-auto mb-8 px-4">
    <!-- Loading -->
    <?php include_once('views/components/loading.php'); ?>

    <div id="property-table" class="flex flex-col">
        <div class="-m-1.5 overflow-x-auto">
            <div class="p-1.5 min-w-full inline-block align-middle">
                <div class="">
                    <table class="min-w-full divide-y divide-gray-200 table-responsive">
                        <thead>
                            <tr>
                                <th scope="col" class="px-4 py-3 text-start">
                                    <label for="hs-at-with-checkboxes-main" class="flex">
                                        <input id="select-all" onclick="toggleCheckboxes(this)" type="checkbox" class="shrink-0 border-gray-300 rounded text-blue-600 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none" id="hs-at-with-checkboxes-main">
                                        <span class="sr-only">Checkbox</span>
                                    </label>
                                </th>
                                <th scope="col" class="px-3 py-3 text-start text-xs font-medium text-gray-500 uppercase">Actions</th>
                                <th scope="col" class="px-3 py-3 text-start text-xs font-medium text-gray-500 uppercase">Reference</th>
                                <th scope="col" class="px-3 py-3 text-start text-xs font-medium text-gray-500 uppercase">Title Deed</th>
                                <th scope="col" class="px-3 py-3 text-start text-xs font-medium text-gray-500 uppercase max-w-[300px]">Property Details</th>
                                <th scope="col" class="px-3 py-3 text-start text-xs font-medium text-gray-500 uppercase">Unit Type</th>
                                <th scope="col" class="px-3 py-3 text-start text-xs font-medium text-gray-500 uppercase">Size</th>
                                <th scope="col" class="px-3 py-3 text-start text-xs font-medium text-gray-500 uppercase">Price</th>
                                <th scope="col" class="px-3 py-3 text-start text-xs font-medium text-gray-500 uppercase">Unit Status</th>
                                <th scope="col" class="px-3 py-3 text-start text-xs font-medium text-gray-500 uppercase">PF</th>
                                <th scope="col" class="px-3 py-3 text-start text-xs font-medium text-gray-500 uppercase">Bayut</th>
                                <th scope="col" class="px-3 py-3 text-start text-xs font-medium text-gray-500 uppercase">Listing Agent and Owner</th>
                                <th scope="col" class="px-3 py-3 text-start text-xs font-medium text-gray-500 uppercase min-w-[200px]">Published Portals</th>
                                <th scope="col" class="px-3 py-3 text-start text-xs font-medium text-gray-500 uppercase">Created On</th>
                            </tr>
                        </thead>
                        <tbody id="property-list" class="divide-y divide-gray-200"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <?php include 'views/components/pagination.php'; ?>

    <!-- Modals -->
    <?php include 'views/modals/filter.php'; ?>
    <?php include 'views/modals/refresh-listing.php'; ?>
    <?php
    // if ($isAdmin) {
    include 'views/modals/transfer-to-agent.php';
    include 'views/modals/transfer-to-owner.php';
    // }
    ?>
</div>


<script>
    let currentPage = 1;
    const pageSize = 50;
    let totalPages = 0;

    const isAdmin = localStorage.getItem('isAdmin') == 'true';

    async function fetchProperties(page = 1, filters = null) {
        const baseUrl = API_BASE_URL;
        const entityTypeId = LISTINGS_ENTITY_TYPE_ID;
        const fields = [
            'id', 'ufCrm15ReferenceNumber', 'ufCrm15OfferingType', 'ufCrm15PropertyType', 'ufCrm15Price', 'ufCrm15TitleEn', 'ufCrm15DescriptionEn', 'ufCrm15Size', 'ufCrm15Bedroom', 'ufCrm15Bathroom', 'ufCrm15PhotoLinks', 'ufCrm15AgentName', 'ufCrm15City', 'ufCrm15Community', 'ufCrm15SubCommunity', 'ufCrm15Tower', 'ufCrm15BayutCity', 'ufCrm15BayutCommunity', 'ufCrm15BayutSubCommunity', 'ufCrm15BayutTower', 'ufCrm15PfEnable', 'ufCrm15BayutEnable', 'ufCrm15DubizzleEnable', 'ufCrm15WebsiteEnable', 'ufCrm15ListingOwner', 'ufCrm15Status', 'ufCrm15RentalPeriod', 'createdTime', 'ufCrm15TitleDeed'
        ];
        const orderBy = {
            id: 'desc'
        };
        const start = (page - 1) * pageSize;

        function buildApiUrl(baseUrl, entityTypeId, fields, orderBy, start, filters) {
            const selectParams = fields.map((field, index) => `select[${index}]=${field}`).join('&');

            const orderParams = Object.entries(orderBy)
                .map(([key, value]) => `order[${key}]=${value}`)
                .join('&');

            if (filters) {
                localStorage.setItem('filters', JSON.stringify(filters));

                const filterParams = Object.entries(filters)
                    .map(([key, value]) => `filter[${key}]=${value}`)
                    .join('&');

                return `${baseUrl}/crm.item.list?entityTypeId=${entityTypeId}&${selectParams}&${orderParams}&start=${start}&${filterParams}`;
            }

            return `${baseUrl}/crm.item.list?entityTypeId=${entityTypeId}&${selectParams}&${orderParams}&start=${start}`;
        }

        // Generate the API URL
        const apiUrl = buildApiUrl(baseUrl, entityTypeId, fields, orderBy, start, filters);

        const loading = document.getElementById('loading');
        const propertyTable = document.getElementById('property-table');
        const propertyList = document.getElementById('property-list');
        const pagination = document.getElementById('pagination');
        const prevPage = document.getElementById('prevPage');
        const nextPage = document.getElementById('nextPage');
        const pageInfo = document.getElementById('pageInfo');

        try {
            loading.classList.remove('hidden');
            propertyTable.classList.add('hidden');
            pagination.classList.add('hidden');


            const response = await fetch(apiUrl, {
                method: 'GET'
            });

            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }

            const data = await response.json();
            const properties = data.result?.items || [];
            const totalCount = data.total || 0;

            document.getElementById('listingCount').textContent = totalCount

            totalPages = Math.ceil(totalCount / pageSize);

            prevPage.disabled = page === 1;
            nextPage.disabled = page === totalPages || totalPages === 0;
            pageInfo.textContent = `Page ${page} of ${totalPages}`;

            propertyList.innerHTML = properties
                .map(
                    (property) => `
                <tr>
                    <td class="size-sm whitespace-nowrap">
                        <div class="ps-6 py-3">
                            <label for="hs-at-with-checkboxes-1" class="flex">
                            <input type="checkbox" name="property_ids[]" value="${property.id}" class="shrink-0 border-gray-300 rounded text-blue-600 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none" id="hs-at-with-checkboxes-1">
                            <span class="sr-only">Checkbox</span>
                            </label>
                        </div>
                    </td>
                    <td class="px-3 py-4 whitespace-nowrap text-xs font-medium text-gray-800">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-transparent dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>
                            <ul class="dropdown-menu shadow absolute z-10" style="max-height: 60vh; overflow-y: auto; scrollbar-width: thin; scrollbar-color: #6B7280 #f9fafb; font-size:medium;">
                                ${isAdmin ? `
                                <li><a class="dropdown-item" href="?page=edit-property&id=${property.id}"><i class="fa-solid fa-edit me-2"></i>Edit</a></li>
                                <li><button class="dropdown-item" onclick="handleAction('duplicate', ${property.id})"><i class="fa-solid fa-copy me-2"></i>Duplicate Listing</button></li>
                                <li>
                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#referenceModal" data-property-id="${property.id}" data-reference="${property.ufCrm15ReferenceNumber}">
                                        <i class="fa-solid fa-sync me-2"></i>Refresh Listing
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                ` : ''}
                                <li><a class="dropdown-item" target="_blank" href="download-pdf.php?type=logged&id=${property.id}"><i class="fa-solid fa-print me-2"></i>Download PDF as Logged-In Agent</a></li>
                                <li><a class="dropdown-item" target="_blank" href="download-pdf.php?type=agent&id=${property.id}"><i class="fa-solid fa-print me-2"></i>Download PDF as Listing Agent</a></li>
                                <li><a class="dropdown-item" target="_blank" href="download-pdf.php?type=owner&id=${property.id}"><i class="fa-solid fa-print me-2"></i>Download PDF as Listing Owner</a></li>
                                ${isAdmin ? `
                                <li><hr class="dropdown-divider"></li>
                                <li><button class="dropdown-item" onclick="handleAction('export-excel', ${property.id})"><i class="fa-solid fa-file-excel me-2"></i>Export as Excel</button></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><button class="dropdown-item" onclick="handleAction('publish', ${property.id})"><i class="fa-solid fa-bullhorn me-2"></i>Publish to all</button></li>
                                <li><button class="dropdown-item" onclick="handleAction('publish', ${property.id}, 'pf')"><i class="fa-solid fa-search me-2"></i>Publish to PF</button></li>
                                <li><button class="dropdown-item" onclick="handleAction('publish', ${property.id}, 'bayut')"><i class="fa-solid fa-building me-2"></i>Publish to Bayut</button></li>
                                <li><button class="dropdown-item" onclick="handleAction('publish', ${property.id}, 'dubizzle')"><i class="fa-solid fa-home me-2"></i>Publish to Dubizzle</button></li>
                                <li><button class="dropdown-item" onclick="handleAction('publish', ${property.id}, 'website')"><i class="fa-solid fa-globe me-2"></i>Publish to Website</button></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><button class="dropdown-item" onclick="handleAction('unpublish', ${property.id})"><i class="fa-solid fa-archive me-2"></i>Unpublish from all</button></li>
                                <li><button class="dropdown-item" onclick="handleAction('unpublish', ${property.id}, 'pf')"><i class="fa-solid fa-search me-2"></i>Unpublish from PF</button></li>
                                <li><button class="dropdown-item" onclick="handleAction('unpublish', ${property.id}, 'bayut')"><i class="fa-solid fa-building me-2"></i>Unpublish from Bayut</button></li>
                                <li><button class="dropdown-item" onclick="handleAction('unpublish', ${property.id}, 'dubizzle')"><i class="fa-solid fa-home me-2"></i>Unpublish from Dubizzle</button></li>
                                <li><button class="dropdown-item" onclick="handleAction('unpublish', ${property.id}, 'website')"><i class="fa-solid fa-globe me-2"></i>Unpublish from Website</button></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><button class="dropdown-item text-danger" onclick="handleAction('archive', ${property.id})"><i class="fa-solid fa-archive me-2"></i>Archive</button></li>
                                <li><button class="dropdown-item text-danger" onclick="handleAction('delete', ${property.id})"><i class="fa-solid fa-trash me-2"></i>Delete</button></li>
                                ` : ''}
                            </ul>
                        </div>
                    </td>
                    <td class="px-3 py-4 whitespace-nowrap text-xs font-medium text-gray-800 text-wrap">${property.ufCrm15ReferenceNumber || 'N/A'}</td>
                    <td class="px-3 py-4 whitespace-nowrap text-xs font-medium text-gray-800 text-wrap">${property.ufCrm15TitleDeed || ''}</td>
                    <td class="px-3 py-4 whitespace-nowrap text-xs font-medium text-gray-800 min-w-[300px]">
                        <div class="flex">
                            <img class="w-20 h-20 rounded object-cover mr-4" src="${property.ufCrm15PhotoLinks[0] || 'https://placehold.jp/150x150.png'}" alt="${property.ufCrm15TitleEn || 'N/A'}">
                            <div class="text-sm">
                                <p class="text-gray-800 font-semibold"><a class="hover:text-black/75 text-black text-semibold text-wrap text-decoration-none" href="?page=view-property&id=${property.id}">${property.ufCrm15TitleEn || 'N/A'}</a></p>
                                <p class="text-gray-400 text-wrap max-w-full truncate">${property.ufCrm15DescriptionEn.slice(0, 60) + '...' || 'N/A'}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-3 py-4 whitespace-nowrap text-xs font-medium text-gray-800">
                        <div class="flex flex-col items-start gap-1">
                            <span class="text-sm text-muted" title="Bathrooms"><i class="fa-solid fa-bath mr-1"></i>${property.ufCrm15Bathroom || 'N/A'}</span>
                            <span class="text-sm text-muted" title="Bedrooms"><i class="fa-solid fa-bed mr-1"></i>${property.ufCrm15Bedroom === 0 ? 'Studio' : property.ufCrm15Bedroom === 11 ? '10+' : property.ufCrm15Bedroom || 'N/A'}</span>
                        </div>
                    </td>
                    <td class="px-3 py-4 whitespace-nowrap text-xs font-medium text-gray-800">
                        <div class="flex flex-col items-start gap-1">
                            <span class="text-sm text-muted" title="Bathrooms"><i class="fa-solid fa-ruler-combined mr-1"></i>${property.ufCrm15Size + ' sqft' || 'N/A'}</span>
                            <span class="text-sm text-muted" title="Bedrooms"><i class="fa-solid fa-ruler-horizontal mr-1"></i>${sqftToSqm(property.ufCrm15Size) + ' sqm' || 'N/A'}</span>
                        </div>
                    </td>
                    <td class="px-3 py-4 whitespace-nowrap text-xs font-medium text-gray-800">
                        ${
                            property.ufCrm15Price 
                                ? `${formatPrice(property.ufCrm15Price)}${property.ufCrm15OfferingType === 'RR' || property.ufCrm15OfferingType === 'CR' 
                                    ? `/${property.ufCrm15RentalPeriod === 'Y' ? 'Year' : property.ufCrm15RentalPeriod === 'M' ? 'Month' : property.ufCrm15RentalPeriod === 'W' ? 'Week' : property.ufCrm15RentalPeriod === 'D' ? 'Day' : ''} - Rent`
                                    : (property.ufCrm15OfferingType === 'CS' || property.ufCrm15OfferingType === 'RS' ? ' - Sale' : '')}`
                                : ''
                        }
                    </td>
                    <td class="px-3 py-4 whitespace-nowrap text-xs font-medium text-gray-800">
                        ${getStatusBadge(property.ufCrm15Status)}
                    </td>
                    <td class="px-3 py-4 whitespace-nowrap text-xs font-medium text-gray-800">
                        <p>
                            ${[
                                property.ufCrm15City,
                                property.ufCrm15Community,
                            ]
                            .filter(Boolean)
                            .join(' - ') || ''}
                        </p>
                        <p>
                            ${[
                                property.ufCrm15SubCommunity,
                                property.ufCrm15Tower
                            ]
                            .filter(Boolean)
                            .join(' - ') || ''}
                        </p>
                    </td>
                    <td class="px-3 py-4 whitespace-nowrap text-xs font-medium text-gray-800">
                        <p>
                            ${[
                                property.ufCrm15BayutCity,
                                property.ufCrm15BayutCommunity,
                            ]
                            .filter(Boolean)
                            .join(' - ') || ''}
                        </p>
                        <p>
                            ${[
                                property.ufCrm15BayutSubCommunity,
                                property.ufCrm15BayutTower
                            ]
                            .filter(Boolean)
                            .join(' - ') || ''}
                        </p>
                    </td>
                    <td class="px-3 py-4 whitespace-nowrap text-xs font-medium text-gray-800">
                        <p class="">${property.ufCrm15AgentName || ''}</p> 
                        <p class="">${property.ufCrm15ListingOwner || ''}</p> 
                    </td>
                   <td class="px-3 py-4 whitespace-nowrap text-xs font-medium text-gray-800">
                        <div class="flex gap-1">
                            ${property.ufCrm15PfEnable === "Y" ? '<img class="w-8 h-8 rounded-full object-cover" src="assets/images/pf.png" alt="Property Finder" title="Property Finder">' : ''}
                            ${property.ufCrm15BayutEnable === "Y" ? '<img class="w-8 h-8 rounded-full object-cover" src="assets/images/bayut.png" alt="Bayut" title="Bayut">' : ''}
                            ${property.ufCrm15DubizzleEnable === "Y" ? '<img class="w-8 h-8 rounded-full object-cover" src="assets/images/dubizzle.png" alt="Dubizzle" title="Dubizzle">' : ''}
                            ${property.ufCrm15WebsiteEnable === "Y" ? '<img class="w-8 h-8 rounded-full object-cover bg-black" src="assets/images/company-logo.png" alt="Mondus Properties" title="Mondus Properties">' : ''}
                        </div>
                    </td>
                    <td class="px-3 py-4 whitespace-nowrap text-xs font-medium text-gray-800">
                        <p class="">${formatDate(property.createdTime) || ''}</p> 
                    </td>

                </tr>`
                )
                .join('');

            return properties;
        } catch (error) {
            console.error('Error fetching properties:', error);
            return [];
        } finally {
            loading.classList.add('hidden');
            propertyTable.classList.remove('hidden');
            pagination.classList.remove('hidden');

        }
    }

    function sqftToSqm(sqft) {
        const sqm = sqft * 0.092903;
        return parseFloat(sqm.toFixed(2));
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        const options = {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        };
        return date.toLocaleDateString('en-US', options);
    }

    function changePage(direction) {
        if (direction === 'prev' && currentPage > 1) {
            currentPage--;
        } else if (direction === 'next' && currentPage < totalPages) {
            currentPage++;
        }
        const filters = JSON.parse(localStorage.getItem('filters'));
        fetchProperties(currentPage, filters || null);
    }

    function toggleCheckboxes(source) {
        const checkboxes = document.querySelectorAll('input[name="property_ids[]"]');

        checkboxes.forEach((checkbox) => {
            checkbox.checked = source.checked;
        });
    }

    function formatPrice(amount, locale = 'en-US', currency = 'AED') {
        if (isNaN(amount)) {
            return 'Invalid amount';
        }

        return new Intl.NumberFormat(locale, {
            style: 'currency',
            currency: currency,
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(amount);
    }

    function getStatusBadge(status) {
        switch (status) {
            case 'PUBLISHED':
                return '<span class="inline-flex items-center gap-x-1.5 py-1.5 px-2 border rounded-full text-xs font-medium bg-green-50 text-green-800">Published</span>';
            case 'UNPUBLISHED':
                return '<span class="inline-flex items-center gap-x-1.5 py-1.5 px-2 border rounded-full text-xs font-medium bg-red-50 text-red-800">Unpublished</span>';
            case 'LIVE':
                return '<span class="inline-flex items-center gap-x-1.5 py-1.5 px-2 border rounded-full text-xs font-medium bg-blue-50 text-blue-800">Live</span>';
            case 'DRAFT':
                return '<span class="inline-flex items-center gap-x-1.5 py-1.5 px-2 border rounded-full text-xs font-medium bg-gray-50 text-gray-800">Draft</span>';
            case 'ARCHIVED':
                return '<span class="inline-flex items-center gap-x-1.5 py-1.5 px-2 border rounded-full text-xs font-medium bg-gray-50 text-gray-800">Archived</span>';
            default:
                return '<span class="inline-flex items-center gap-x-1.5 py-1.5 px-2 border rounded-full text-xs font-medium bg-gray-50 text-gray-800">' + status + '</span>';
        }
    }

    fetchProperties(currentPage);
</script>