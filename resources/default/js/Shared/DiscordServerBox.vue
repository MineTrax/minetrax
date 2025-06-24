<template>
  <div v-if="enabled && server" class="discord-widget w-full rounded-xl border bg-card text-card-foreground shadow">
    <!-- Loading State -->
    <div v-if="loading" class="discord-widget-loading">
      <div class="loading-spinner"></div>
      <p class="loading-text">{{ __("Loading Discord server...") }}</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="discord-widget-error">
      <div class="error-icon">⚠️</div>
      <p class="error-text">{{ error }}</p>
      <button @click="fetchDiscordData" class="retry-button">{{ __("Retry") }}</button>
    </div>

    <!-- Success State -->
    <div v-else-if="discordData" class="discord-widget-content">
      <!-- Server Header -->
      <div class="discord-header">
        <div class="server-info">
          <div class="server-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
              <path d="M20.317 4.3698a19.7913 19.7913 0 00-4.8851-1.5152.0741.0741 0 00-.0785.0371c-.211.3753-.4447.8648-.6083 1.2495-1.8447-.2762-3.68-.2762-5.4868 0-.1636-.3933-.4058-.8742-.6177-1.2495a.077.077 0 00-.0785-.037 19.7363 19.7363 0 00-4.8852 1.515.0699.0699 0 00-.0321.0277C.5334 9.0458-.319 13.5799.0992 18.0578a.0824.0824 0 00.0312.0561c2.0528 1.5076 4.0413 2.4228 5.9929 3.0294a.0777.0777 0 00.0842-.0276c.4616-.6304.8731-1.2952 1.226-1.9942a.076.076 0 00-.0416-.1057c-.6528-.2476-1.2743-.5495-1.8722-.8923a.077.077 0 01-.0076-.1277c.1258-.0943.2517-.1923.3718-.2914a.0743.0743 0 01.0776-.0105c3.9278 1.7933 8.18 1.7933 12.0614 0a.0739.0739 0 01.0785.0095c.1202.099.246.1981.3728.2924a.077.077 0 01-.0066.1276 12.2986 12.2986 0 01-1.873.8914.0766.0766 0 00-.0407.1067c.3604.698.7719 1.3628 1.225 1.9932a.076.076 0 00.0842.0286c1.961-.6067 3.9495-1.5219 6.0023-3.0294a.077.077 0 00.0313-.0552c.5004-5.177-.8382-9.6739-3.5485-13.6604a.061.061 0 00-.0312-.0286zM8.02 15.3312c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9555-2.4189 2.157-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419-.0190 1.3332-.9555 2.4189-2.1569 2.4189zm7.9748 0c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9554-2.4189 2.1569-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.9555 2.4189-2.1568 2.4189Z"/>
            </svg>
          </div>
          <div class="server-details">
            <a
              v-if="discordData.instant_invite"
              :href="discordData.instant_invite"
              target="_blank"
              rel="noopener noreferrer"
              class="server-name-link"
            >
              <h3 class="server-name">{{ discordData.name }}</h3>
            </a>
            <h3 v-else class="server-name">{{ discordData.name }}</h3>
            <p class="member-count">{{ __(":count members online", { count: discordData.presence_count }) }}</p>
          </div>
        </div>
      </div>

      <!-- Voice Channels -->
      <div v-if="voiceChannels.length > 0" class="channels-section">
        <h4 class="section-title">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
            <path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>
          </svg>
          {{ __("VOICE CHANNELS") }} — {{ voiceChannels.length }}
        </h4>
        <div class="channels-list">
          <div
            v-for="channel in voiceChannels.slice(0, 4)"
            :key="channel.id"
            class="channel-item"
          >
            <div class="channel-indicator"></div>
            <div class="channel-content">
              <div class="channel-name">{{ channel.name }}</div>
              <!-- Members in this voice channel -->
              <div v-if="getMembersInChannel(channel.id).length > 0" class="voice-members">
                <div
                  v-for="member in getMembersInChannel(channel.id)"
                  :key="member.id"
                  class="voice-member"
                >
                  <div class="member-avatar small">
                    <img
                      v-if="member.avatar_url"
                      :src="member.avatar_url"
                      :alt="member.username"
                      loading="lazy"
                      @error="handleImageError"
                    />
                    <div v-else class="avatar-placeholder small">
                      {{ member.username.charAt(0).toUpperCase() }}
                    </div>
                    <div :class="['status-indicator', 'small', member.status]"></div>
                  </div>
                  <span class="voice-member-name">{{ member.username }}</span>
                </div>
              </div>
            </div>
          </div>
          <div v-if="voiceChannels.length > 4" class="more-channels">
            {{ __("+:count more channels", { count: voiceChannels.length - 4 }) }}
          </div>
        </div>
      </div>

      <!-- Members Section -->
      <div class="members-container">
        <!-- Online Members -->
        <div v-if="onlineMembers.length > 0" class="members-section">
          <h4 class="section-title online">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
              <circle cx="12" cy="12" r="10"/>
            </svg>
            {{ __("ONLINE") }}
          </h4>
          <div class="members-list">
            <div
              v-for="member in onlineMembers.slice(0, 8)"
              :key="member.id"
              class="member-item"
            >
              <div class="member-avatar">
                <img
                  v-if="member.avatar_url"
                  :src="member.avatar_url"
                  :alt="member.username"
                  loading="lazy"
                  @error="handleImageError"
                />
                <div v-else class="avatar-placeholder">
                  {{ member.username.charAt(0).toUpperCase() }}
                </div>
                <div class="status-indicator online"></div>
              </div>
              <div class="member-info">
                <div class="member-name">{{ member.username }}</div>
                <div v-if="member.game" class="member-activity">
                  <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor">
                    <circle cx="12" cy="12" r="3"/>
                  </svg>
                  {{ member.game.name }}
                </div>
              </div>
            </div>
          </div>
          <div v-if="onlineMembers.length > 8" class="more-members">
            {{ __("and more...") }}
          </div>
        </div>

        <!-- Idle Members -->
        <div v-if="idleMembers.length > 0" class="members-section">
          <h4 class="section-title idle">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
              <circle cx="12" cy="12" r="10"/>
              <path d="M12 6v6l4 2"/>
            </svg>
            {{ __("IDLE") }}
          </h4>
          <div class="members-list">
            <div
              v-for="member in idleMembers.slice(0, 6)"
              :key="member.id"
              class="member-item"
            >
              <div class="member-avatar">
                <img
                  v-if="member.avatar_url"
                  :src="member.avatar_url"
                  :alt="member.username"
                  loading="lazy"
                  @error="handleImageError"
                />
                <div v-else class="avatar-placeholder">
                  {{ member.username.charAt(0).toUpperCase() }}
                </div>
                <div class="status-indicator idle"></div>
              </div>
              <div class="member-info">
                <div class="member-name">{{ member.username }}</div>
                <div v-if="member.game" class="member-activity">
                  <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor">
                    <circle cx="12" cy="12" r="3"/>
                  </svg>
                  {{ member.game.name }}
                </div>
              </div>
            </div>
          </div>
          <div v-if="idleMembers.length > 6" class="more-members">
            {{ __("and more...") }}
          </div>
        </div>

        <!-- DND Members -->
        <div v-if="dndMembers.length > 0" class="members-section">
          <h4 class="section-title dnd">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
              <circle cx="12" cy="12" r="10"/>
              <path d="M15 9H9v6h6z"/>
            </svg>
            {{ __("DO NOT DISTURB") }}
          </h4>
          <div class="members-list">
            <div
              v-for="member in dndMembers.slice(0, 6)"
              :key="member.id"
              class="member-item"
            >
              <div class="member-avatar">
                <img
                  v-if="member.avatar_url"
                  :src="member.avatar_url"
                  :alt="member.username"
                  loading="lazy"
                  @error="handleImageError"
                />
                <div v-else class="avatar-placeholder">
                  {{ member.username.charAt(0).toUpperCase() }}
                </div>
                <div class="status-indicator dnd"></div>
              </div>
              <div class="member-info">
                <div class="member-name">{{ member.username }}</div>
                <div v-if="member.game" class="member-activity">
                  <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor">
                    <circle cx="12" cy="12" r="3"/>
                  </svg>
                  {{ member.game.name }}
                </div>
              </div>
            </div>
          </div>
          <div v-if="dndMembers.length > 6" class="more-members">
            {{ __("and more...") }}
          </div>
        </div>
      </div>

      <!-- Join Button at Bottom -->
      <div class="join-section">
        <a
          v-if="discordData.instant_invite"
          :href="discordData.instant_invite"
          target="_blank"
          rel="noopener noreferrer"
          class="join-button"
        >
          {{ __("Join Discord") }}
          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
            <path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/>
          </svg>
        </a>
      </div>
    </div>
  </div>
</template>

<script>
export default {
    props: {
        enabled: Boolean,
        server: String,
    },
    data() {
        return {
      colorMode: window.colorMode,
      loading: false,
      error: null,
      discordData: null,
      refreshInterval: null
    }
  },
  computed: {
    voiceChannels() {
      if (!this.discordData?.channels) return []
      return this.discordData.channels
        .filter(channel => channel.name.includes('🎙') || channel.name.includes('voice') || channel.name.includes('vc'))
        .sort((a, b) => a.position - b.position)
    },
    onlineMembers() {
      if (!this.discordData?.members) return []
      return this.discordData.members.filter(member => member.status === 'online' && !member.channel_id)
    },
    idleMembers() {
      if (!this.discordData?.members) return []
      return this.discordData.members.filter(member => member.status === 'idle' && !member.channel_id)
    },
    dndMembers() {
      if (!this.discordData?.members) return []
      return this.discordData.members.filter(member => member.status === 'dnd' && !member.channel_id)
    }
  },
  mounted() {
    if (this.enabled && this.server) {
      this.fetchDiscordData()
      // Refresh data every 30 seconds
      this.refreshInterval = setInterval(() => {
        this.fetchDiscordData(true)
      }, 30000)
    }
  },
  beforeUnmount() {
    if (this.refreshInterval) {
      clearInterval(this.refreshInterval)
    }
  },
  methods: {
    async fetchDiscordData(silent = false) {
      if (!silent) {
        this.loading = true
        this.error = null
      }

      try {
        const response = await fetch(`https://discord.com/api/guilds/${this.server}/widget.json`)

        if (!response.ok) {
          if (response.status === 404) {
            throw new Error('Discord server not found or widget not enabled')
          } else if (response.status === 403) {
            throw new Error('Discord server widget is disabled')
          } else {
            throw new Error(`Discord API error: ${response.status} ${response.statusText}`)
          }
        }

        const data = await response.json()

        if (!data || typeof data !== 'object') {
          throw new Error('Invalid response from Discord API')
        }

        this.discordData = data
        this.error = null
      } catch (err) {
        console.error('Error fetching Discord data:', err)

        if (err.name === 'TypeError' && err.message.includes('fetch')) {
          this.error = 'Network error: Unable to connect to Discord'
        } else if (err.name === 'SyntaxError') {
          this.error = 'Invalid response from Discord API'
        } else {
          this.error = err.message || 'Failed to load Discord server'
        }

        this.discordData = null
      } finally {
        this.loading = false
      }
    },
    getMembersInChannel(channelId) {
      if (!this.discordData?.members) return []
      return this.discordData.members.filter(member => member.channel_id === channelId)
    },
    handleImageError(event) {
      // Hide broken images
      event.target.style.display = 'none'
    }
  }
}
</script>

<style scoped>
.discord-widget {
  max-width: 400px;
  margin: 0 auto;
  overflow: hidden;
  font-family: var(--font-sans);
}

.discord-widget-content {
  border-radius: inherit;
}

.discord-widget-loading,
.discord-widget-error {
  text-align: center;
  padding: 40px 20px;
}

.loading-spinner {
  width: 32px;
  height: 32px;
  border: 3px solid hsl(var(--muted));
  border-radius: 50%;
  border-top-color: hsl(var(--primary));
  animation: spin 1s ease-in-out infinite;
  margin: 0 auto 16px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.loading-text {
  margin: 0;
  color: hsl(var(--muted-foreground));
  font-size: 14px;
}

.error-icon {
  font-size: 32px;
  margin-bottom: 12px;
}

.error-text {
  margin: 0 0 16px;
  color: hsl(var(--muted-foreground));
  font-size: 14px;
}

.retry-button {
  background: hsl(var(--primary));
  border: none;
  color: hsl(var(--primary-foreground));
  padding: 8px 16px;
  border-radius: var(--radius);
  cursor: pointer;
  font-size: 14px;
  transition: background-color 0.2s ease;
}

.retry-button:hover {
  background: hsl(var(--primary) / 0.9);
}

.discord-header {
  background: hsl(var(--secondary));
  padding: 12px 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid hsl(var(--border));
  border-radius: var(--radius) var(--radius) 0 0;
}

.server-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.server-icon {
  width: 40px;
  height: 40px;
  background: hsl(var(--primary));
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: hsl(var(--primary-foreground));
  flex-shrink: 0;
}

.server-details {
  min-width: 0;
  flex: 1;
}

.server-name-link {
  text-decoration: none;
  color: inherit;
  display: block;
  transition: opacity 0.2s ease;
}

.server-name-link:hover {
  opacity: 0.8;
}

.server-name {
  margin: 0 0 2px;
  font-size: 15px;
  font-weight: 600;
  color: hsl(var(--card-foreground));
  line-height: 1.2;
}

.member-count {
  margin: 0;
  font-size: 13px;
  color: hsl(var(--muted-foreground));
}

.channels-section {
  padding: 12px 16px;
  border-bottom: 1px solid hsl(var(--border));
}

.section-title {
  margin: 0 0 8px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: hsl(var(--muted-foreground));
  display: flex;
  align-items: center;
  gap: 6px;
}

.section-title.online {
  color: hsl(142 76% 36%);
}

.section-title.idle {
  color: hsl(45 93% 47%);
}

.section-title.dnd {
  color: hsl(0 84% 60%);
}

.channels-list {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.channel-item {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  padding: 6px 8px;
  border-radius: var(--radius);
  font-size: 13px;
  color: hsl(var(--card-foreground));
  transition: background-color 0.2s ease;
  background: hsl(var(--muted) / 0.3);
}

.channel-item:hover {
  background: hsl(var(--muted) / 0.5);
}

.channel-indicator {
  width: 6px;
  height: 6px;
  background: hsl(142 76% 36%);
  border-radius: 50%;
  flex-shrink: 0;
  margin-top: 4px;
}

.channel-icon {
  color: hsl(var(--muted-foreground));
  flex-shrink: 0;
}

.channel-name {
  color: hsl(var(--card-foreground));
  font-weight: 500;
}

.more-channels,
.more-members {
  padding: 4px 8px;
  font-size: 11px;
  color: hsl(var(--muted-foreground));
  text-align: left;
  margin-top: 4px;
  background: hsl(var(--muted) / 0.2);
  border-radius: var(--radius);
}

.members-container {
  max-height: 300px;
  overflow-y: auto;
}

.members-section {
  padding: 12px 16px;
  border-bottom: 1px solid hsl(var(--border));
}

.members-section:last-child {
  border-bottom: none;
}

.members-list {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.member-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 4px 8px;
  border-radius: var(--radius);
  transition: background-color 0.2s ease;
  background: hsl(var(--muted) / 0.2);
}

.member-item:hover {
  background: hsl(var(--muted) / 0.4);
}

.member-avatar {
  position: relative;
  flex-shrink: 0;
}

.member-avatar img,
.avatar-placeholder {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  object-fit: cover;
}

.avatar-placeholder {
  background: hsl(var(--accent));
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 600;
  color: hsl(var(--accent-foreground));
}

.status-indicator {
  position: absolute;
  bottom: -1px;
  right: -1px;
  width: 10px;
  height: 10px;
  border-radius: 50%;
  border: 2px solid hsl(var(--card));
}

.status-indicator.online {
  background: hsl(142 76% 36%);
}

.status-indicator.idle {
  background: hsl(45 93% 47%);
}

.status-indicator.dnd {
  background: hsl(0 84% 60%);
}

.member-info {
  flex: 1;
  min-width: 0;
}

.member-name {
  font-size: 13px;
  font-weight: 500;
  color: hsl(var(--card-foreground));
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-bottom: 1px;
}

.member-activity {
  font-size: 11px;
  color: hsl(var(--muted-foreground));
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  display: flex;
  align-items: center;
  gap: 3px;
}

.join-section {
  padding: 12px 16px;
  border-top: 1px solid hsl(var(--border));
  background: hsl(var(--muted) / 0.2);
}

.join-button {
  background: hsl(var(--background));
  color: hsl(var(--foreground));
  text-decoration: none;
  padding: 10px 16px;
  border-radius: var(--radius);
  font-size: 14px;
  font-weight: 500;
  transition: background-color 0.2s ease;
  border: 1px solid hsl(var(--border));
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  width: 100%;
}

.join-button:hover {
  background: hsl(var(--accent));
}

/* Custom scrollbar for members container */
.members-container::-webkit-scrollbar {
  width: 4px;
}

.members-container::-webkit-scrollbar-track {
  background: hsl(var(--muted) / 0.2);
}

.members-container::-webkit-scrollbar-thumb {
  background: hsl(var(--muted-foreground) / 0.3);
  border-radius: var(--radius);
}

.members-container::-webkit-scrollbar-thumb:hover {
  background: hsl(var(--muted-foreground) / 0.5);
}

/* Responsive Design */
@media (min-width: 768px) {
  .discord-widget {
    max-width: 400px;
  }
}

/* Voice Channel Members */
.channel-content {
  flex: 1;
  min-width: 0;
}

.voice-members {
  display: flex;
  flex-direction: column;
  gap: 4px;
  margin-top: 8px;
  padding-left: 16px;
}

.voice-member {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 2px 6px;
  border-radius: var(--radius);
  font-size: 12px;
  background: hsl(var(--muted) / 0.1);
  transition: background-color 0.2s ease;
}

.voice-member:hover {
  background: hsl(var(--muted) / 0.2);
}

.voice-member-name {
  color: hsl(var(--card-foreground));
  font-weight: 500;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.member-avatar.small img,
.avatar-placeholder.small {
  width: 20px;
  height: 20px;
}

.avatar-placeholder.small {
  font-size: 10px;
}

.status-indicator.small {
  width: 6px;
  height: 6px;
  border-width: 1px;
  bottom: 0;
  right: 0;
}

/* Smooth transitions */
.discord-widget * {
  transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
}
</style>
