# 🚀 FlightHub Assignment - Vercel Deployment Guide

## Quick Deploy to Vercel

### 1. Connect GitHub to Vercel
1. Go to [vercel.com](https://vercel.com) and sign up/login
2. Connect your GitHub account
3. Import your `FlightHub-Assignment` repository

### 2. Configure the Project
1. Set **Root Directory** to: `apps/Backend`
2. Vercel will auto-detect Laravel
3. Deploy!

### 3. Set Environment Variables
Set these in your Vercel project settings:

```bash
DB_HOST=your-database-host
DB_PORT=5432
DB_DATABASE=flighthub
DB_USERNAME=your-username
DB_PASSWORD=your-password
DB_CONNECTION=pgsql
DB_SSLMODE=require
APP_KEY=your-app-key
APP_URL=https://your-vercel-domain.vercel.app
```

### 4. Access Your App
- **Backend API**: `https://your-project.vercel.app`
- **Frontend**: Deploy separately or use the same domain

### 5. Automatic Deployments
1. Every push to `main` branch
2. Vercel automatically redeploys
3. Preview deployments for pull requests

## Why Vercel?
- ✅ **Simpler deployment** - No Docker complexity
- ✅ **Automatic environment variables** - Easy configuration
- ✅ **Built-in Laravel support** - Optimized for PHP apps
- ✅ **Global CDN** - Fast worldwide performance
- ✅ **Free tier** - Generous hosting allowance
