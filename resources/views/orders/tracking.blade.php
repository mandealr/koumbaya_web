@extends('layouts.app')

@section('title', 'Suivi de mes commandes')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Suivi de mes commandes</h1>
        <p class="text-gray-600">Suivez l'état de vos commandes et billets de tombola</p>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Commandes</p>
                    <p class="text-2xl font-semibold text-gray-900" id="total-orders">-</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Complétées</p>
                    <p class="text-2xl font-semibold text-gray-900" id="completed-orders">-</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">En attente</p>
                    <p class="text-2xl font-semibold text-gray-900" id="pending-orders">-</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Billets Achetés</p>
                    <p class="text-2xl font-semibold text-gray-900" id="total-tickets">-</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres et recherche -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Recherche</label>
                <input type="text" id="search-input" placeholder="Numéro de commande ou ticket..." 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                <select id="status-filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Tous les statuts</option>
                    <option value="completed">Complété</option>
                    <option value="pending">En attente</option>
                    <option value="payment_initiated">Paiement initié</option>
                    <option value="failed">Échoué</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                <select id="type-filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Tous les types</option>
                    <option value="ticket_purchase">Achat de billet</option>
                    <option value="direct_purchase">Achat direct</option>
                </select>
            </div>
            
            <div class="flex items-end">
                <button id="search-btn" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-200">
                    Rechercher
                </button>
            </div>
        </div>
    </div>

    <!-- Liste des commandes -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Mes commandes</h2>
        </div>
        
        <div id="orders-container">
            <!-- Les commandes seront chargées ici via JavaScript -->
            <div class="p-8 text-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                <p class="text-gray-500 mt-4">Chargement des commandes...</p>
            </div>
        </div>
        
        <!-- Pagination -->
        <div id="pagination-container" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            <!-- La pagination sera générée via JavaScript -->
        </div>
    </div>

    <!-- Modal de détails de commande -->
    <div id="order-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Détails de la commande</h3>
                    <button id="close-modal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div id="order-details">
                    <!-- Les détails de la commande seront chargés ici -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 1;
    let currentFilters = {};

    // Charger les statistiques
    loadStats();
    
    // Charger les commandes
    loadOrders();

    // Event listeners pour les filtres
    document.getElementById('search-btn').addEventListener('click', function() {
        currentPage = 1;
        loadOrders();
    });

    // Event listeners pour les modals
    document.getElementById('close-modal').addEventListener('click', function() {
        document.getElementById('order-modal').classList.add('hidden');
    });

    // Fonction pour charger les statistiques
    async function loadStats() {
        try {
            const response = await fetch('/api/orders/stats', {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                    'Accept': 'application/json'
                }
            });
            
            if (response.ok) {
                const result = await response.json();
                const stats = result.data;
                
                document.getElementById('total-orders').textContent = stats.total_orders;
                document.getElementById('completed-orders').textContent = stats.completed_orders;
                document.getElementById('pending-orders').textContent = stats.pending_orders;
                document.getElementById('total-tickets').textContent = stats.total_tickets_purchased;
            }
        } catch (error) {
            console.error('Erreur lors du chargement des statistiques:', error);
        }
    }

    // Fonction pour charger les commandes
    async function loadOrders(page = 1) {
        currentPage = page;
        
        const params = new URLSearchParams({
            page: page,
            per_page: 10
        });

        const search = document.getElementById('search-input').value;
        const status = document.getElementById('status-filter').value;
        const type = document.getElementById('type-filter').value;

        if (search) params.append('search', search);
        if (status) params.append('status', status);
        if (type) params.append('type', type);

        try {
            const response = await fetch(`/api/orders?${params.toString()}`, {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                    'Accept': 'application/json'
                }
            });
            
            if (response.ok) {
                const result = await response.json();
                displayOrders(result.data);
                displayPagination(result.pagination);
            } else {
                showError('Erreur lors du chargement des commandes');
            }
        } catch (error) {
            console.error('Erreur:', error);
            showError('Erreur de connexion');
        }
    }

    // Fonction pour afficher les commandes
    function displayOrders(orders) {
        const container = document.getElementById('orders-container');
        
        if (orders.length === 0) {
            container.innerHTML = `
                <div class="p-8 text-center">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2 2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-4.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 009.586 13H7"></path>
                    </svg>
                    <p class="text-gray-500">Aucune commande trouvée</p>
                </div>
            `;
            return;
        }

        const ordersHtml = orders.map(order => `
            <div class="border-b border-gray-200 hover:bg-gray-50 transition duration-200">
                <div class="px-6 py-4">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3 mb-2">
                                <h3 class="text-lg font-medium text-gray-900">#${order.transaction_id}</h3>
                                <span class="status-badge status-${order.status}">${getStatusText(order.status)}</span>
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">${getTypeText(order.type)}</span>
                            </div>
                            
                            <div class="text-sm text-gray-600 mb-2">
                                ${order.product ? order.product.name : 'Produit supprimé'}
                                ${order.lottery ? `- Tombola #${order.lottery.lottery_number}` : ''}
                            </div>
                            
                            <div class="flex items-center space-x-4 text-sm text-gray-500">
                                <span>${formatDate(order.created_at)}</span>
                                <span>${order.amount} ${order.currency}</span>
                                ${order.quantity ? `<span>${order.quantity} billet(s)</span>` : ''}
                            </div>
                        </div>
                        
                        <div class="flex space-x-2">
                            <button onclick="viewOrderDetails('${order.transaction_id}')" 
                                    class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                                Détails
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');

        container.innerHTML = ordersHtml;
    }

    // Fonction pour afficher les détails d'une commande
    window.viewOrderDetails = async function(transactionId) {
        try {
            const response = await fetch(`/api/orders/${transactionId}`, {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                    'Accept': 'application/json'
                }
            });
            
            if (response.ok) {
                const result = await response.json();
                displayOrderDetails(result.data);
                document.getElementById('order-modal').classList.remove('hidden');
            } else {
                showError('Erreur lors du chargement des détails');
            }
        } catch (error) {
            console.error('Erreur:', error);
            showError('Erreur de connexion');
        }
    };

    // Fonction pour afficher les détails dans le modal
    function displayOrderDetails(order) {
        const container = document.getElementById('order-details');
        
        let ticketsHtml = '';
        if (order.tickets && order.tickets.length > 0) {
            ticketsHtml = `
                <div class="mt-6">
                    <h4 class="text-md font-medium text-gray-900 mb-3">Billets de tombola</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        ${order.tickets.map(ticket => `
                            <div class="border rounded-lg p-4 ${ticket.is_winner ? 'bg-green-50 border-green-200' : 'bg-gray-50'}">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-medium">${ticket.ticket_number}</p>
                                        <p class="text-sm text-gray-600">${ticket.price_paid} ${order.currency}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="status-badge status-${ticket.status}">${getStatusText(ticket.status)}</span>
                                        ${ticket.is_winner ? '<p class="text-sm text-green-600 mt-1">🎉 Gagnant!</p>' : ''}
                                    </div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        }

        container.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-md font-medium text-gray-900 mb-3">Informations de la commande</h4>
                    <dl class="space-y-2">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Référence:</dt>
                            <dd class="text-sm font-medium">${order.transaction_id}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Statut:</dt>
                            <dd><span class="status-badge status-${order.status}">${getStatusText(order.status)}</span></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Type:</dt>
                            <dd class="text-sm">${getTypeText(order.type)}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Montant:</dt>
                            <dd class="text-sm font-medium">${order.amount} ${order.currency}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Méthode de paiement:</dt>
                            <dd class="text-sm">${order.payment_method || 'Non spécifié'}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Date de création:</dt>
                            <dd class="text-sm">${formatDate(order.created_at)}</dd>
                        </div>
                        ${order.completed_at ? `
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Date de completion:</dt>
                            <dd class="text-sm">${formatDate(order.completed_at)}</dd>
                        </div>
                        ` : ''}
                    </dl>
                </div>
                
                <div>
                    ${order.product ? `
                    <h4 class="text-md font-medium text-gray-900 mb-3">Produit</h4>
                    <div class="border rounded-lg p-4">
                        <h5 class="font-medium">${order.product.name}</h5>
                        <p class="text-sm text-gray-600 mt-1">${order.product.description || ''}</p>
                    </div>
                    ` : ''}
                    
                    ${order.lottery ? `
                    <h4 class="text-md font-medium text-gray-900 mb-3 mt-6">Tombola</h4>
                    <div class="border rounded-lg p-4">
                        <h5 class="font-medium">${order.lottery.title}</h5>
                        <div class="text-sm text-gray-600 mt-2">
                            <p>Numéro: ${order.lottery.lottery_number}</p>
                            <p>Prix du billet: ${order.lottery.ticket_price} ${order.currency}</p>
                            <p>Billets vendus: ${order.lottery.sold_tickets}/${order.lottery.max_tickets}</p>
                            ${order.lottery.draw_date ? `<p>Date du tirage: ${formatDate(order.lottery.draw_date)}</p>` : ''}
                        </div>
                    </div>
                    ` : ''}
                </div>
            </div>
            
            ${ticketsHtml}
        `;
    }

    // Fonction pour afficher la pagination
    function displayPagination(pagination) {
        const container = document.getElementById('pagination-container');
        
        if (pagination.last_page <= 1) {
            container.innerHTML = '';
            return;
        }

        const prevDisabled = pagination.current_page <= 1 ? 'disabled' : '';
        const nextDisabled = pagination.current_page >= pagination.last_page ? 'disabled' : '';

        container.innerHTML = `
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    Affichage de ${((pagination.current_page - 1) * pagination.per_page) + 1} à 
                    ${Math.min(pagination.current_page * pagination.per_page, pagination.total)} 
                    sur ${pagination.total} résultats
                </div>
                
                <div class="flex space-x-1">
                    <button onclick="loadOrders(${pagination.current_page - 1})" 
                            ${prevDisabled}
                            class="px-3 py-1 border rounded ${prevDisabled ? 'text-gray-400 cursor-not-allowed' : 'text-gray-700 hover:bg-gray-100'}">
                        Précédent
                    </button>
                    
                    ${generatePageNumbers(pagination)}
                    
                    <button onclick="loadOrders(${pagination.current_page + 1})" 
                            ${nextDisabled}
                            class="px-3 py-1 border rounded ${nextDisabled ? 'text-gray-400 cursor-not-allowed' : 'text-gray-700 hover:bg-gray-100'}">
                        Suivant
                    </button>
                </div>
            </div>
        `;
    }

    // Fonctions utilitaires
    function getStatusText(status) {
        const statusMap = {
            'completed': 'Complété',
            'pending': 'En attente',
            'payment_initiated': 'Paiement initié',
            'failed': 'Échoué',
            'paid': 'Payé',
            'refunded': 'Remboursé'
        };
        return statusMap[status] || status;
    }

    function getTypeText(type) {
        const typeMap = {
            'ticket_purchase': 'Achat de billet',
            'direct_purchase': 'Achat direct'
        };
        return typeMap[type] || type;
    }

    function formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('fr-FR', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function generatePageNumbers(pagination) {
        let html = '';
        const current = pagination.current_page;
        const last = pagination.last_page;
        
        for (let i = Math.max(1, current - 2); i <= Math.min(last, current + 2); i++) {
            const activeClass = i === current ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-100';
            html += `<button onclick="loadOrders(${i})" class="px-3 py-1 border rounded ${activeClass}">${i}</button>`;
        }
        
        return html;
    }

    function showError(message) {
        // Simple toast notification
        const toast = document.createElement('div');
        toast.className = 'fixed top-4 right-4 bg-red-600 text-white px-4 py-2 rounded shadow-lg z-50';
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 3000);
    }
});
</script>

<style>
.status-badge {
    @apply px-2 py-1 text-xs rounded-full font-medium;
}

.status-completed {
    @apply bg-green-100 text-green-800;
}

.status-pending {
    @apply bg-yellow-100 text-yellow-800;
}

.status-payment_initiated {
    @apply bg-blue-100 text-blue-800;
}

.status-failed {
    @apply bg-red-100 text-red-800;
}

.status-paid {
    @apply bg-green-100 text-green-800;
}

.status-refunded {
    @apply bg-gray-100 text-gray-800;
}

button:disabled {
    @apply cursor-not-allowed opacity-50;
}
</style>
@endsection