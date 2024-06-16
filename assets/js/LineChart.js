class LineChart {
    constructor(canvasId, categories, annualExpenses, annualIncomes, monthlyData) {
        this.canvasId = canvasId;
        this.categories = categories;
        this.annualExpenses = annualExpenses;
        this.annualIncomes = annualIncomes;
        this.monthlyData = monthlyData;
    }

    init() {
        // Ordenar os dados mensais por mês, do mais antigo para o mais recente
        this.monthlyData.sort((a, b) => parseInt(a.month) - parseInt(b.month));

        const ctx = document.getElementById(this.canvasId).getContext('2d');
        const months = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: this.monthlyData.map(month => months[parseInt(month.month) - 1]),
                datasets: [{
                    label: 'Despesas Mensais',
                    data: this.monthlyData.map(month => month.total_expense),
                    backgroundColor: 'rgba(220, 53, 69, 0.2)',
                    borderColor: 'rgba(220, 53, 69, 1)',
                    borderWidth: 1,
                    fill: false
                }, {
                    label: 'Ganhos Mensais',
                    data: this.monthlyData.map(month => month.total_income),
                    backgroundColor: 'rgba(40, 167, 69, 0.2)',
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 1,
                    fill: false
                }]
            },
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                }
            }
        });
    }
}
