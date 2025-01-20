<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';
import { createUpload } from '@mux/upchunk'; //responsible for uploads

defineProps({
    files:Array
})
// this constant file holds the value of ref that will be stored in it from the binding in the form
const file = ref(null)

const initialState = reactive({
    file:null,
    uploader:null,
    progress:0,
    uploading:false,
    error:null
})

const state = reactive({
    ...initialState, //this automatically spreads the initialState data to the state variable
    // rounded off the state.progress
    formattedProgress: computed(() => Math.round(state.progress)),
    reset: () => {
        Object.assign(state, initialState)
    }
})
// cancel the upload
const cancel = () => {
    state.uploader.abort()
    state.reset()
}

const submit = () => {
    // contains the file uploaded
    state.file = file.value.files[0]
    if(!state.file){
        return
    }
    // console.log(state.file)
    // this is the api, responsible for creating the uploads and push the files uploaded to the controller recieving them via the route defined in there
    state.uploader = createUpload({
        endpoint:route('files.store'),//the api also has an endpoint to where it is pushing the file uploaded
        // the csrf_token is passed on from the inertiahelper
        headers:{//this is the header of the api that doesnt come by default with the api, but has to be imported
            'X-CSRF-TOKEN': usePage().props.csrf_token
        },
        method:'post', //it contains the method you are going to use to push the files
        file:state.file,//this api contains a file parameter that holds the file or files you are uploading
        chunkSize: 10 * 1024 //10mbs - it contains the size of the uploads
    })
    // states on uploading process
    state.uploader.on('attempt', (p) => {
        state.error = null,//reset any errors
        state.uploading = true
    })

    state.uploader.on('progress', (p) => {
        state.progress = p.detail
    })

    state.uploader.on('success', () => {
        state.reset()
        // reload only the files part of the route
        router.reload({
            only:['files'],
            preserveScroll:true
        })
    })

    state.uploader.on('error', (error) => {
        state.error = error.detail.message
    })
}
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-3">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form class="p-6 text-gray-900 space-y-6" v-on:submit.prevent="submit">
                        <div class="flex items-center overflow-hidden">
                            <!-- this is the ref value that is referenced from the constant ref -->
                            <input type="file" name="file" ref="file" class="flex-grow">
                            <PrimaryButton>Upload</PrimaryButton>
                        </div>

                        <div v-if="state.uploading">
                            <div class="bg-gray-100 shadow-inner h-3 rounded overflow-hidden">
                                <div class="bg-blue-500 h-full transition-all duration-200" v-bind:style="{width:state.progress + '%'}"></div>
                            </div>
                        </div>

                        <div v-if="state.uploading" class="flex items-center justify-between">
                            <div class="flex-grow">
                                <div class="flex items-center space-x-2">
                                    <button class="text-sm text-indigo-500" v-if="!state.uploader.paused" v-on:click="state.uploader.pause()">Pause</button>
                                    <button class="text-sm text-indigo-500" v-if="state.uploader.paused" v-on:click="state.uploader.resume()">Resume</button>
                                    <button class="text-sm text-indigo-500" v-on:click="cancel">cancel</button>
                                </div>
                            </div>
                            <div>
                                {{ state.formattedProgress }}%
                            </div>
                        </div>

                        <div v-if="state.error">
                            {{ state.error }}
                        </div>
                    </form>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <template v-if="files.length">
                            <div v-for="file in files" :key="file.id">
                                {{ file.file_path }}
                            </div>
                        </template>

                        <template v-else>
                            No files yet.
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
