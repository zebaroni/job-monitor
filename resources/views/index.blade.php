<!DOCTYPE html>
<html lang="en">
<head>
    <title>JobMonitor - Dashboard</title>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width"/>
    <link rel="stylesheet" href="https://unpkg.com/primevue/resources/themes/aura-dark-green/theme.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/primeicons@7.0.0/primeicons.min.css">
</head>
<body>
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script src="https://unpkg.com/primevue/core/core.min.js"></script>

<script src="https://unpkg.com/primevue/datatable/datatable.min.js"></script>
<script src="https://unpkg.com/primevue/column/column.min.js"></script>
<script src="https://unpkg.com/primevue/card/card.min.js"></script>
<script src="https://unpkg.com/primevue/inputtext/inputtext.min.js"></script>
<script src="https://unpkg.com/primevue/divider/divider.min.js"></script>
<script src="https://unpkg.com/primevue/button/button.min.js"></script>

<div id="app" class="app-wrapper">
    <div id="header">
        <h2>JobMonitor - Dashboard</h2>
        <div @click="refreshData"
             class="refresh-button"
             title="Refresh Data">
            <i class="pi pi-refresh"></i>
        </div>
    </div>
    <div>
        <p-divider/>
    </div>
    <div id="content-wrapper">
        <div id="sidebar">
            <div :class="selectedMenuItem === 'overview' ? 'menu-item menu-item-active' : 'menu-item'"
                 @click="selectedMenuItem = 'overview'">
                <i class="pi pi-chart-bar"></i>
                Overview
            </div>
            <div :class="selectedMenuItem === 'workers' ? 'menu-item menu-item-active' : 'menu-item'"
                 @click="selectedMenuItem = 'workers'">
                <i class="pi pi-server"></i>
                Workers
            </div>
            <div :class="selectedMenuItem === 'jobs' ? 'menu-item menu-item-active' : 'menu-item'"
                 @click="selectedMenuItem = 'jobs'">
                <i class="pi pi-bars"></i>
                Jobs
            </div>
        </div>
        <div id="main-content">
            <div id="overview-content" v-if="selectedMenuItem === 'overview'">
                <p>TODO: Add charts</p>
            </div>

            <div id="workers-content" v-if="selectedMenuItem === 'workers'">
                <p-card>
                    <template #title>
                        Active Workers
                        <p-divider/>
                    </template>
                    <template #content>
                        <p-datatable :value="workers" :size="'medium'" paginator :rows="15">
                            <template #empty>No workers currently active</template>

                            <p-column sortable field="worker" header="Worker"></p-column>
                            <p-column sortable field="pid" header="PID"></p-column>
                            <p-column sortable field="job_type" header="Job"></p-column>
                            <p-column sortable field="job_id" header="Job ID"></p-column>
                            <p-column sortable field="time_elapsed_ms" header="Time Elapsed (ms)">
                                <template #body="slotProps">
                                    @{{ slotProps.data.time_elapsed_ms.toFixed(2) }}
                                </template>
                            </p-column>
                        </p-datatable>
                    </template>
                </p-card>
            </div>

            <div id="jobs-content" v-if="selectedMenuItem === 'jobs'">
                <p-card>
                    <template #title>
                        <div style="display: flex; flex-direction: row; justify-content: space-between">
                            <p style="margin: 0">Jobs Metrics</p>
                        </div>
                        <p-divider/>
                    </template>
                    <template #content>
                        <p-datatable stripedRows :value="metrics" :size="'medium'" paginator :rows="15">
                            <template #empty>No jobs being monitored</template>

                            <p-column sortable field="job_type" header="Job"></p-column>
                            <p-column sortable field="total_jobs_processed" header="Total"></p-column>
                            <p-column sortable field="time_peak_ms" header="Time Peak (ms)">
                                <template #body="slotProps">
                                    @{{ slotProps.data.time_peak_ms.toFixed(2) }}
                                </template>
                            </p-column>
                            <p-column sortable field="time_avg_ms" header="Time Avg. (ms)">
                                <template #body="slotProps">
                                    @{{ slotProps.data.time_avg_ms.toFixed(2) }}
                                </template>
                            </p-column>
                            <p-column sortable field="cpu_peak_ms" header="CPU Peak (ms)">
                                <template #body="slotProps">
                                    @{{ slotProps.data.cpu_peak_ms.toFixed(2) }}
                                </template>
                            </p-column>
                            <p-column sortable field="cpu_avg_ms" header="CPU Avg. (ms)">
                                <template #body="slotProps">
                                    @{{ slotProps.data.cpu_avg_ms.toFixed(2) }}
                                </template>
                            </p-column>
                            <p-column sortable field="memory_peak_mb" header="RAM Peak (mb)">
                                <template #body="slotProps">
                                    @{{ slotProps.data.memory_peak_mb.toFixed(2) }}
                                </template>
                            </p-column>
                            <p-column sortable field="memory_avg_mb" header="RAM Avg. (mb)">
                                <template #body="slotProps">
                                    @{{ slotProps.data.memory_avg_mb.toFixed(2) }}
                                </template>
                            </p-column>
                            <p-column sortable field="payload_avg_kb" header="Payload Avg. (kb)">
                                <template #body="slotProps">
                                    @{{ slotProps.data.payload_avg_kb.toFixed(2) }}
                                </template>
                            </p-column>
                            <p-column sortable field="payload_peak_kb" header="Payload Peak (kb)">
                                <template #body="slotProps">
                                    @{{ slotProps.data.payload_peak_kb.toFixed(2) }}
                                </template>
                            </p-column>
                        </p-datatable>
                    </template>
                </p-card>
            </div>
        </div>
    </div>
</div>

<script>
    const {createApp, ref, onMounted} = Vue;

    const app = createApp({
        setup() {
            const isRefreshing = ref(false);
            const metrics = ref([]);
            const workers = ref([]);
            const selectedMenuItem = ref("overview");

            async function refreshData() {
                if (isRefreshing.value) {
                    return;
                }

                await getOverviewData();
            }

            async function getOverviewData() {
                isRefreshing.value = true;

                const data = await fetch('/job-monitor/overview');
                const overviewData = await data.json();

                metrics.value = overviewData.metrics;
                workers.value = Object.entries(overviewData.workers)
                    .flatMap(([key, valueArray]) =>
                        valueArray.map((obj) => ({...obj, worker: key.replace('job_worker:', '')}))
                    );

                isRefreshing.value = false;
            }

            onMounted(() => {
                getOverviewData();

                setInterval(() => {
                    getOverviewData();
                }, 10000)
            })

            return {
                metrics,
                workers,
                isRefreshing,
                selectedMenuItem,
                refreshData,
            };
        },
    });

    app.use(primevue.config.default);

    app.component('p-datatable', primevue.datatable);
    app.component('p-column', primevue.column);
    app.component('p-card', primevue.card);
    app.component('p-input-text', primevue.inputtext);
    app.component('p-divider', primevue.divider);
    app.component('p-button', primevue.button);

    app.mount('#app');
</script>
<style>
    body {
        margin: 0;
    }

    .p-datatable-wrapper {
        font-size: 14px !important;
    }

    .p-card-title {
        font-size: 16px
    }

    .app-wrapper {
        padding-right: 100px;
        padding-left: 100px;
        padding-top: 20px;
        padding-bottom: 20px;
        display: flex;
        flex: 1;
        flex-direction: column;
        height: 100vh
    }

    #header {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
    }

    #content-wrapper {
        display: flex;
        flex: 1;
        flex-direction: row;
        gap: 20px;
    }

    #sidebar {
        width: 200px;
    }

    #main-content {
        flex: 1;
    }

    .menu-item {
        margin-top: 5px;
        padding: 10px;
        cursor: pointer;
        border-radius: 8px;
        flex-direction: row;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .menu-item > i {
        opacity: 0.3
    }

    .menu-item:hover {
        background-color: rgba(255, 255, 255, 0.05)
    }

    .menu-item-active {
        background-color: rgba(255, 255, 255, 0.05) !important;
    }

    .refresh-button {
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border-radius: 5px;
        border: 1px solid white;
        padding: 5px
    }

    .refresh-button:hover {
        background-color: rgba(255, 255, 255, 0.05);
    }

    #overview-content {
        display: grid;
        gap: 10px;
        grid-template-columns: 1fr 1fr 1fr;
    }
</style>
</body>
</html>