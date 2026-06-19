<template>
    <MainLayout>
        <div class="min-h-screen bg-[#0a0a0f] text-[#e4e4e7]">
            <!-- Hero -->
            <div class="text-center py-12 px-4 bg-gradient-to-b from-[#00ffa8]/[0.06] to-transparent">
                <h1 class="text-3xl md:text-5xl font-black tracking-tight mb-2">
                    대한민국 커뮤니티 <span class="bg-gradient-to-r from-[#00ffa8] to-[#00cc88] bg-clip-text text-transparent">실시간 인기글</span>
                </h1>
                <p class="text-[#71717a] text-sm">22개 커뮤니티의 베스트 글을 한눈에 모아보세요</p>
                <div class="flex justify-center gap-6 mt-4 text-xs text-[#52525b]">
                    <span>📊 {{ totalPosts }}개 게시글</span>
                    <span>🏛️ 22개 커뮤니티</span>
                    <span>🕐 {{ currentTime }}</span>
                </div>
            </div>

            <div class="max-w-5xl mx-auto px-4">
                <!-- Evening Briefing -->
                <div class="p-6 rounded-2xl mb-6 bg-gradient-to-br from-[#00ffa8]/[0.08] to-[#00cc88]/[0.04] border border-[#00ffa8]/[0.15]">
                    <div class="text-[#00ffa8] text-xs font-semibold">🌙 이브닝 브리핑 · 2026년 5월 25일</div>
                    <h3 class="text-lg font-bold mt-1 mb-2">오늘의 커뮤니티 핵심 이슈</h3>
                    <p class="text-sm text-[#a1a1aa] leading-relaxed">
                        정용진 논란, 스타벅스 비판, 연예계 이슈 및 정치적 갈등이 주요 화제로 부상.<br>
                        에펨코리아·디시인사이드 중심으로 축구·연예 관련 글이 높은 반응.
                    </p>
                </div>

                <!-- Real-time Posts -->
                <section class="mb-10">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold flex items-center gap-2">
                            🔥 실시간 커뮤니티 인기글
                            <span class="text-xs text-[#00ffa8] bg-[#00ffa8]/[0.1] px-2.5 py-0.5 rounded-full">{{ posts.length }}</span>
                        </h2>
                        <a href="#" class="text-xs text-[#71717a] hover:text-[#00ffa8] transition-colors">전체보기 →</a>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <div v-for="(post, idx) in posts" :key="idx"
                            class="flex items-center gap-3 px-4 py-3.5 rounded-xl bg-white/[0.03] border border-white/[0.06] hover:bg-white/[0.06] hover:border-[#00ffa8]/[0.2] hover:translate-x-1 transition-all cursor-pointer">
                            <div class="w-7 text-center text-sm font-bold shrink-0"
                                :class="{
                                    'text-amber-400': idx === 0,
                                    'text-gray-400': idx === 1,
                                    'text-amber-600': idx === 2,
                                    'text-[#52525b]': idx >= 3
                                }">{{ post.rank }}</div>
                            <span class="text-[10px] font-semibold px-2.5 py-1 rounded-full bg-[#00ffa8]/[0.08] text-[#00ffa8] whitespace-nowrap shrink-0">{{ post.community }}</span>
                            <div class="flex-1 text-sm font-medium truncate">{{ post.title }}</div>
                            <div class="flex gap-3 text-[11px] text-[#71717a] shrink-0">
                                <span class="text-[#a1a1aa]">👁 {{ post.views }}</span>
                                <span class="text-blue-400">💬 {{ post.comments > 0 ? post.comments.toLocaleString() : '-' }}</span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Stats -->
                <section class="mb-10">
                    <h2 class="text-lg font-bold mb-4">📊 오늘의 통계</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div v-for="stat in stats" :key="stat.label" class="text-center p-5 rounded-xl bg-white/[0.03] border border-white/[0.06]">
                            <div class="text-2xl font-extrabold text-[#00ffa8]">{{ stat.num }}</div>
                            <div class="text-xs text-[#71717a] mt-1">{{ stat.label }}</div>
                        </div>
                    </div>
                </section>

                <!-- Community Rankings -->
                <section class="mb-10">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold flex items-center gap-2">
                            🏆 커뮤니티 순위
                            <span class="text-xs text-[#00ffa8] bg-[#00ffa8]/[0.1] px-2.5 py-0.5 rounded-full">MAU 기준</span>
                        </h2>
                        <a href="#" class="text-xs text-[#71717a] hover:text-[#00ffa8] transition-colors">전체보기 →</a>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <div v-for="r in rankings.slice(0, 10)" :key="r.rank"
                            class="flex items-center gap-4 px-5 py-4 rounded-xl bg-white/[0.03] border border-white/[0.06] hover:bg-white/[0.06] hover:border-[#00ffa8]/[0.25] transition-all">
                            <div class="w-11 text-center text-lg font-extrabold shrink-0"
                                :class="r.rank <= 3 ? 'bg-gradient-to-br from-[#00ffa8] to-[#00cc88] bg-clip-text text-transparent' : 'text-[#52525b]'">
                                {{ r.rank }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-bold">{{ r.name }}</div>
                                <div class="text-[11px] text-[#52525b]">{{ r.domain }}</div>
                                <div class="flex gap-1 mt-1">
                                    <span class="text-[9px] px-2 py-0.5 rounded-full bg-white/[0.05] text-[#71717a]">{{ r.category }}</span>
                                </div>
                            </div>
                            <div class="text-[11px]" :class="r.trend === 'up' ? 'text-[#00ffa8]' : 'text-red-500'">
                                {{ r.trend === 'up' ? '▲' : '▼' }}
                            </div>
                            <div class="text-xl font-extrabold text-[#00ffa8] shrink-0 min-w-[70px] text-right">
                                {{ r.mau }} <span class="text-xs font-normal text-[#71717a]">M</span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- YouTube + Shopping Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
                    <section>
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-bold">▶️ 유튜브 인기 동영상</h2>
                            <a href="#" class="text-xs text-[#71717a] hover:text-[#00ffa8]">전체보기 →</a>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <div v-for="v in youtube" :key="v.title"
                                class="flex gap-3 p-3 rounded-xl bg-white/[0.03] border border-white/[0.06] hover:bg-white/[0.06] hover:border-[#00ffa8]/[0.2] transition-all items-center">
                                <div class="w-[80px] h-[45px] rounded-lg shrink-0 bg-gradient-to-br from-[#1a1a2e] to-[#16213e] flex items-center justify-center text-lg">▶️</div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-xs font-medium truncate">{{ v.title }}</div>
                                    <div class="text-[10px] text-[#71717a] mt-1">{{ v.channel }} · {{ v.views }}회</div>
                                </div>
                            </div>
                        </div>
                    </section>
                    <section>
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-bold">🛍️ 인기 쇼핑 상품</h2>
                            <a href="#" class="text-xs text-[#71717a] hover:text-[#00ffa8]">전체보기 →</a>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div v-for="s in shopping" :key="s.name"
                                class="p-3 rounded-xl bg-white/[0.03] border border-white/[0.06] hover:bg-white/[0.06] hover:border-[#00ffa8]/[0.2] transition-all text-center">
                                <div class="text-sm font-bold text-[#00ffa8]">{{ s.price }}</div>
                                <div class="text-[11px] text-[#a1a1aa] mt-1">{{ s.name }}</div>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Trends -->
                <section class="mb-10">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold">📈 실시간 트렌드</h2>
                        <a href="#" class="text-xs text-[#71717a] hover:text-[#00ffa8]">전체보기 →</a>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div v-for="t in trends" :key="t.keyword"
                            class="p-4 rounded-xl bg-white/[0.03] border border-white/[0.06] hover:bg-white/[0.06] hover:border-[#00ffa8]/[0.2] transition-all">
                            <div class="text-sm font-semibold"># {{ t.keyword }}</div>
                            <div class="text-[11px] text-[#71717a] mt-1">{{ t.desc }}</div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import MainLayout from '@/layouts/MainLayout.vue'

const currentTime = ref('')
const totalPosts = ref(393)

const posts = ref([
    { rank: 1, community: '에펨코리아', title: '[브라이튼 v 맨유] 브페 21어시 도르구 선제골', views: '1000K', comments: 404 },
    { rank: 2, community: '네이트판', title: '데이식스 도운이랑 유혜주 동생 사귄다는데', views: '165K', comments: 166 },
    { rank: 3, community: '인스티즈', title: '(충격주의) 이혼과정 현실 알려주는 이지현..JPG', views: '127K', comments: 150 },
    { rank: 4, community: '더쿠', title: '멤버들이랑 찐친이 되긴 힘든거 같다는 아이돌들', views: '113K', comments: 600 },
    { rank: 5, community: '디시인사이드', title: '[싱갤] 연예인 오렌지캬라멜 레이나 근황', views: '53K', comments: 520 },
    { rank: 6, community: '루리웹', title: '일본 1위 최대 기업 도요타 근황.JPG', views: '52K', comments: 154 },
    { rank: 7, community: '웃긴대학', title: '도대체 12kg 대형견이 뭔소리임', views: '50K', comments: 54 },
    { rank: 8, community: '뽐뿌', title: '숙박 종사자가 겪어본 각 나라 손님 특징 jpg', views: '45K', comments: 56 },
    { rank: 9, community: '엠엘비파크', title: '"한국 연구진이 해냈다" 인류 구원 임박', views: '27K', comments: 46 },
    { rank: 10, community: '이토랜드', title: '약후)) 뜬금없이 노모가 나왔네요...', views: '27K', comments: 54 },
])

const rankings = ref([
    { rank: 1, name: '디시인사이드', domain: 'dcinside.com', mau: '283', category: '종합커뮤니티', trend: 'up' },
    { rank: 2, name: '에펨코리아', domain: 'fmkorea.com', mau: '147', category: '종합커뮤니티', trend: 'up' },
    { rank: 3, name: '루리웹', domain: 'ruliweb.com', mau: '68', category: '게임', trend: 'up' },
    { rank: 4, name: '더쿠', domain: 'theqoo.net', mau: '61', category: '여성', trend: 'up' },
    { rank: 5, name: '인벤', domain: 'inven.co.kr', mau: '52', category: '게임', trend: 'up' },
    { rank: 6, name: '아카라이브', domain: 'arca.live', mau: '50', category: '종합', trend: 'up' },
    { rank: 7, name: '엠팍', domain: 'mlbpark.donga.com', mau: '45', category: '스포츠', trend: 'down' },
    { rank: 8, name: '뽐뿌', domain: 'ppomppu.co.kr', mau: '43', category: '쇼핑', trend: 'up' },
    { rank: 9, name: '네이트 판', domain: 'pann.nate.com', mau: '33', category: '여성', trend: 'up' },
    { rank: 10, name: '클리앙', domain: 'clien.net', mau: '32', category: '종합커뮤니티', trend: 'up' },
])

const stats = ref([
    { num: '393', label: '분석 게시글' },
    { num: '22', label: '커뮤니티' },
    { num: '1.2M', label: '총 조회수' },
    { num: '4.2K', label: '총 댓글' },
])

const youtube = ref([
    { title: 'NewJeans - Supernatural (Official Video)', channel: 'NewJeans', views: '2.1M' },
    { title: '아이브 신곡 라이브 무대 | 음악중심', channel: 'MBC K-POP', views: '1.8M' },
    { title: 'BTS 정국 - 배드 가이 커버', channel: 'BANGTANTV', views: '1.5M' },
    { title: '한국 연구진이 개발한 초전도체의 충격적 진실', channel: '과학교양', views: '890K' },
    { title: '정용진 탱크데이 논란 총정리', channel: '시사Pick', views: '750K' },
    { title: '연예계 매니저가 고백하는 충격적인 진실', channel: '연예뒷담', views: '620K' },
])

const shopping = ref([
    { name: '에어팟 맥스 (블루)', price: '589,000원' },
    { name: '나이키 에어포스 1', price: '119,000원' },
    { name: '갤럭시 S26 울트라', price: '1,599,000원' },
    { name: 'LG 퓨리케어 공기청정기', price: '329,000원' },
    { name: '아이패드 프로 M5', price: '1,499,000원' },
    { name: '삼성 오디세이 OLED G8', price: '1,290,000원' },
])

const trends = ref([
    { keyword: '정용진', desc: '탱크데이 논란 관련 6.2K 건' },
    { keyword: '스타벅스', desc: '가격 인상 및 서비스 불만 4.8K 건' },
    { keyword: '데이식스', desc: '도운 열애설 관련 3.5K 건' },
    { keyword: '초전도체', desc: '한국 연구진 개발 2.9K 건' },
    { keyword: '이혼전문변호사', desc: '이지현 이혼과정 공개 2.1K 건' },
    { keyword: '오렌지캬라멜', desc: '레이나 근황 조회수 급등 1.8K 건' },
])

let timer
onMounted(() => {
    updateTime()
    timer = setInterval(updateTime, 1000)
})
onUnmounted(() => clearInterval(timer))

function updateTime() {
    const now = new Date()
    currentTime.value = now.toLocaleString('ko-KR', {
        timeZone: 'Asia/Seoul',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    })
}
</script>
