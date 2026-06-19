<template>
    <MainLayout>
        <div
            v-if="!isLoading && !error && profileStore.id"
            class="pt-[30px] px-5 align-center xl:max-w-7xl xl:mx-auto"
        >
            <ProfileHeader />

            <!-- DADA Coin Balance -->
            <div v-if="dadaBalance !== null && profileStore.isSelf" 
                 class="flex items-center justify-between px-4 py-4 mt-2 mx-0 rounded-2xl bg-gradient-to-r from-amber-50 via-yellow-50 to-orange-50 dark:from-amber-900/30 dark:via-yellow-900/30 dark:to-orange-900/30 border border-amber-200/80 dark:border-amber-700/50 shadow-lg shadow-amber-200/30 dark:shadow-amber-900/20 hover:shadow-xl hover:shadow-amber-300/40 dark:hover:shadow-amber-800/30 transition-all duration-300 hover:-translate-y-0.5">
                <div class="flex items-center gap-2">
                    <span class="text-2xl">🪙</span>
                    <div>
                        <div class="text-sm font-semibold text-amber-800 dark:text-amber-300">DADA Coin</div>
                        <div class="text-xs text-amber-600 dark:text-amber-400">Minima blockchain</div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-xl font-bold text-amber-800 dark:text-amber-200">{{ formatDada(dadaBalance) }}</div>
                    <div v-if="dadaLoading" class="text-xs text-amber-500 animate-pulse">loading...</div>
                </div>
            </div>

            <ProfileTabBar
                :show-private-tabs="
                    authStore.authenticated && profileStore.id === authStore.getUser?.id
                "
                @tab-change="handleTabChange"
                @filter-change="handleFilterChange"
                ref="tabBarRef"
            />

            <ProfilePlaylists v-if="playlists && playlists.length" :playlists="playlists" />

            <div v-if="show" class="mt-6 grid lg:grid-cols-4 md:grid-cols-3 grid-cols-2 gap-4">
                <div v-for="post in displayPosts" :key="post.id">
                    <ProfileVideoCard :post="post" />
                </div>
                <div class="w-full h-20"></div>
            </div>

            <div v-if="profileStore.isLoadingMorePosts" class="flex justify-center py-12">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#F02C56] to-purple-600 flex items-center justify-center animate-pulse">
                    <svg class="w-6 h-6 text-white animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </div>
            </div>

            <div v-else-if="profileStore.relationship.blocking" class="flex justify-center py-8">
                <p class="text-gray-500 dark:text-gray-400 text-sm">You blocked this account.</p>
            </div>

            <div
                v-else-if="displayPosts && displayPosts.length > 16 && !profileStore.hasMorePosts"
                class="flex justify-center py-8"
            >
                <p class="text-gray-500 dark:text-gray-400 text-sm">
                    {{ $t('profile.noMorePostsToLoad') }}
                </p>
            </div>

            <div
                v-else-if="
                    currentTab === 'bookmarks' &&
                    displayPosts &&
                    displayPosts.length === 0 &&
                    !profileStore.isLoadingMorePosts
                "
                class="flex flex-col items-center justify-center py-16"
            >
                <div class="text-6xl mb-4">
                    <BookmarkIcon class="w-20 h-20" />
                </div>
                <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-2">
                    {{ $t('profile.favouritePosts') }}
                </h3>
                <p class="text-gray-500 dark:text-gray-400 text-center">
                    {{ $t('profile.yourFavouritePostsWillAppearHere') }}
                </p>
            </div>

            <div
                v-else-if="
                    displayPosts && displayPosts.length === 0 && !profileStore.isLoadingMorePosts
                "
                class="flex flex-col items-center justify-center py-16"
            >
                <div class="text-6xl mb-4">📹</div>
                <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-2">
                    {{ $t('profile.noVideosYet') }}
                </h3>
                <p class="text-gray-500 dark:text-gray-400 text-center">
                    {{
                        profileStore.isSelf
                            ? $t('profile.youHaventPostedAnyVideosYet')
                            : `@${$t('profile.userHasntPostedAnyVideosYet', { username: profileStore.username })}`
                    }}
                </p>
            </div>
        </div>

        <div v-else-if="isLoading" class="pt-[90px] px-5 overflow-hidden">
            <div class="flex flex-col items-center justify-center min-h-[400px]">
                <Spinner />
                <p class="text-gray-500 dark:text-gray-400 mt-4 text-sm">
                    {{ $t('profile.loadingProfileDotDotDot') }}
                </p>
            </div>
        </div>

        <div v-else-if="error" class="pt-[90px] px-5 overflow-hidden">
            <div
                class="flex flex-col items-center justify-center min-h-[400px] max-w-md mx-auto text-center"
            >
                <div class="text-6xl mb-6">😵</div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-3">
                    {{
                        error.type === 'not-found'
                            ? $t('profile.profileNotFound')
                            : $t('common.somethingWentWrong')
                    }}
                </h2>
                <p
                    class="text-gray-600 dark:text-gray-400 mb-6 leading-relaxed"
                    v-html="error.message"
                ></p>

                <div class="space-y-3 w-full">
                    <button
                        @click="retryLoad"
                        :disabled="retryLoading"
                        class="w-full bg-blue-500 hover:bg-blue-600 disabled:bg-blue-300 disabled:cursor-not-allowed text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200 flex items-center justify-center"
                    >
                        <Spinner v-if="retryLoading" class="w-4 h-4 mr-2" />
                        <span>{{
                            retryLoading ? $t('common.retryingDotDotDot') : $t('common.tryAgain')
                        }}</span>
                    </button>

                    <button
                        @click="$router.push('/')"
                        class="w-full bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-semibold py-3 px-6 rounded-lg transition-colors duration-200"
                    >
                        {{ $t('common.goToHome') }}
                    </button>
                </div>
            </div>
        </div>
        <ReportModal />
    </MainLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { storeToRefs } from 'pinia'
import MainLayout from '~/layouts/MainLayout.vue'
import ProfileVideoCard from '~/components/Profile/ProfileVideoCard.vue'
import ProfilePlaylists from '~/components/Profile/ProfilePlaylists.vue'
import { useProfileStore } from '~/stores/profile'
import { useAuthStore } from '~/stores/auth'
import { useUtils } from '@/composables/useUtils'
import { useI18n } from 'vue-i18n'
import { useHead } from '@unhead/vue'
import { BookmarkIcon } from '@heroicons/vue/24/outline'

const { formatCount } = useUtils()
const authStore = useAuthStore()
const profileStore = useProfileStore()

const route = useRoute()
const router = useRouter()

const { t } = useI18n()
const show = ref(false)
const showFollowersModal = ref(false)
const showEditModal = ref(false)
const isLoading = ref(false)
const error = ref(null)
const retryLoading = ref(false)
const currentTab = ref('videos')
const currentFilter = ref('Latest')
const tabBarRef = ref(null)
const dadaBalance = ref(null)
const dadaLoading = ref(false)

const { posts, allLikes, bookmarkedPosts, playlists } = storeToRefs(profileStore)

const displayPosts = computed(() => {
    if (currentTab.value === 'bookmarks') {
        return bookmarkedPosts.value || []
    }
    return posts.value || []
})

const metaTitle = computed(() => {
    if (!profileStore.name) return 'Loops'
    return `${profileStore.name} (@${profileStore.username}) | Loops`
})

const metaDescription = computed(() => {
    if (!profileStore.username) return 'Watch videos on Loops'

    const parts = []

    if (profileStore.bio) {
        parts.push(profileStore.bio)
    }

    const stats = [
        `${formatCount(profileStore.postCount)} videos`,
        `${formatCount(profileStore.followerCount)} followers`,
        `${formatCount(profileStore.allLikes)} likes`
    ]

    parts.push(stats.join(' · '))

    return parts.join(' | ')
})

const profileUrl = computed(() => {
    if (!profileStore.username) return ''
    return `${window.location.origin}/@${profileStore.username}`
})

const profileAvatar = computed(() => {
    return profileStore.avatar || '/storage/avatars/default.jpg'
})

useHead({
    title: metaTitle,
    meta: [
        {
            name: 'description',
            content: metaDescription
        },
        {
            property: 'og:title',
            content: metaTitle
        },
        {
            property: 'og:description',
            content: metaDescription
        },
        {
            property: 'og:image',
            content: profileAvatar
        },
        {
            property: 'og:url',
            content: profileUrl
        },
        {
            property: 'og:type',
            content: 'profile'
        },
        {
            property: 'profile:username',
            content: () => profileStore.username || ''
        },
        {
            name: 'twitter:card',
            content: 'summary'
        },
        {
            name: 'twitter:title',
            content: metaTitle
        },
        {
            name: 'twitter:description',
            content: metaDescription
        },
        {
            name: 'twitter:image',
            content: profileAvatar
        }
    ]
})

let scrollTimeout = null

const handleTabChange = async (tab) => {
    currentTab.value = tab

    if (tab === 'bookmarks' && profileStore.isSelf) {
        try {
            profileStore.isLoadingMorePosts = true
            await profileStore.getBookmarkedPosts()
        } catch (error) {
            console.error('Error loading bookmarked posts:', error)
        } finally {
            profileStore.isLoadingMorePosts = false
        }
    }
}

const handleFilterChange = async (filter) => {
    profileStore.isLoadingMorePosts = true
    currentFilter.value = filter

    if (currentTab.value === 'bookmarks') {
        await profileStore.getBookmarkedPosts(filter).finally(() => {
            profileStore.isLoadingMorePosts = false
        })
    } else {
        await profileStore.updateSort(filter).finally(() => {
            profileStore.isLoadingMorePosts = false
        })
    }
}

const openEditProfile = () => {
    showEditModal.value = true
}

const gotoProfile = (id) => {
    showFollowersModal.value = false
    router.push(`/@${id}`)
}

const sanitize = (s) =>
    s.replace(
        /[<>&"']/g,
        (c) => ({ '<': '&lt;', '>': '&gt;', '&': '&amp;', '"': '&quot;', "'": '&#39;' })[c]
    )

const loadProfileData = async (userId) => {
    try {
        isLoading.value = true
        error.value = null
        show.value = false

        await profileStore.getProfileAndPosts(userId)
    } catch (err) {
        console.error('Error loading profile:', err)

        if (err.response?.status === 404) {
            error.value = {
                type: 'not-found',
                message: t('profile.profile404ErrorMessage', {
                    userid: sanitize(userId)
                })
            }
        } else if ([500, 502, 503].includes(err.response?.status)) {
            error.value = {
                type: 'server-error',
                message: t('profile.profile500ErrorMessage')
            }
        } else if (!navigator.onLine) {
            error.value = {
                type: 'network-error',
                message: t('profile.profileOfflineErrorMessage')
            }
        } else {
            error.value = {
                type: 'unknown-error',
                message: t('profile.profileUnknownErrorMessage')
            }
        }
    } finally {
        isLoading.value = false
    }
}

const retryLoad = async () => {
    retryLoading.value = true
    try {
        await loadProfileData(route.params.id)
    } finally {
        retryLoading.value = false
    }
}

const handleScroll = () => {
    if (scrollTimeout) {
        clearTimeout(scrollTimeout)
    }

    scrollTimeout = setTimeout(() => {
        if (profileStore.isLoadingMorePosts || !profileStore.hasMorePosts) {
            return
        }

        const scrollTop = window.pageYOffset || document.documentElement.scrollTop
        const windowHeight = window.innerHeight
        const documentHeight = document.documentElement.scrollHeight

        const threshold = 300
        const distanceFromBottom = documentHeight - (scrollTop + windowHeight)

        if (distanceFromBottom < threshold) {
            loadMorePosts()
        }
    }, 100)
}

const loadMorePosts = async () => {
    if (!profileStore.id) return

    try {
        if (currentTab.value === 'bookmarks') {
            await profileStore.loadMoreBookmarkedPosts()
        } else {
            await profileStore.loadMorePosts(profileStore.id)
        }
    } catch (error) {
        console.error('Error loading more posts:', error)
    }
}

const formatDada = (val) => {
    if (val === null || val === undefined) return '0'
    return Number(val).toLocaleString()
}

const fetchDadaBalance = async () => {
    if (!authStore.authenticated) return
    try {
        dadaLoading.value = true
        const axios = (await import('~/plugins/axios')).default
        const instance = axios.getAxiosInstance()
        const res = await instance.get('/api/v1/dada/balance')
        dadaBalance.value = res.data.balance
    } catch (e) {
        console.error('Failed to fetch DADA balance:', e)
        dadaBalance.value = 0
    } finally {
        dadaLoading.value = false
    }
}

onMounted(async () => {
    await loadProfileData(route.params.id)
    fetchDadaBalance()
    window.addEventListener('scroll', handleScroll)
})

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll)
    if (scrollTimeout) {
        clearTimeout(scrollTimeout)
    }
})

watch(
    () => route.params.id,
    (newId) => {
        if (newId) {
            loadProfileData(newId)
            currentTab.value = 'videos'
        }
    }
)

watch(
    () => displayPosts.value,
    () => {
        setTimeout(() => (show.value = true), 300)
    }
)
</script>
