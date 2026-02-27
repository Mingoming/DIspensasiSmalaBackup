<script setup>
import { ref, onMounted, watch } from 'vue'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
} from 'chart.js'
import { Line } from 'vue-chartjs'

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
)

const props = defineProps({
  data: {
    type: Object,
    required: true
  }
})

const chartData = ref({
  labels: [],
  datasets: []
})

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'top',
    },
    title: {
      display: false
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      ticks: {
        stepSize: 1
      }
    }
  }
}

function updateChartData() {
  if (!props.data || !props.data.data) return

  chartData.value = {
    labels: props.data.data.map(item => item.month_name.substring(0, 3)),
    datasets: [
      {
        label: 'Total',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        borderColor: 'rgb(59, 130, 246)',
        data: props.data.data.map(item => item.total),
        fill: true,
        tension: 0.4
      },
      {
        label: 'Disetujui',
        backgroundColor: 'rgba(34, 197, 94, 0.1)',
        borderColor: 'rgb(34, 197, 94)',
        data: props.data.data.map(item => item.approved),
        fill: true,
        tension: 0.4
      },
      {
        label: 'Ditolak',
        backgroundColor: 'rgba(239, 68, 68, 0.1)',
        borderColor: 'rgb(239, 68, 68)',
        data: props.data.data.map(item => item.rejected),
        fill: true,
        tension: 0.4
      }
    ]
  }
}

watch(() => props.data, updateChartData, { deep: true })

onMounted(() => {
  updateChartData()
})
</script>

<template>
  <div class="h-64 sm:h-80">
    <Line :data="chartData" :options="chartOptions" />
  </div>
</template>