// assets/js/MonthlyPieChart.js

class MonthlyPieChart {
    constructor(canvasId, expenseAmount, incomeAmount) {
        this.canvasId = canvasId;
        this.expenseAmount = expenseAmount;
        this.incomeAmount = incomeAmount;
    }

    init() {
        const ctx = document.getElementById(this.canvasId).getContext('2d');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Despesas', 'Ganhos'],
                datasets: [{
                    label: 'Resumo Mensal',
                    data: [this.expenseAmount, this.incomeAmount],
                    backgroundColor: ['#dc3545', '#28a745']
                }]
            }
        });
    }
}
