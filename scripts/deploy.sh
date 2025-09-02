#!/bin/bash

# FlightHub Assignment - Deployment Script
# This script helps you deploy your FlightHub application

echo "🚀 FlightHub Assignment - Deployment Guide"
echo "=========================================="
echo ""

echo "📋 Prerequisites:"
echo "   - GitHub repository connected to Vercel"
echo "   - PostgreSQL database (you can keep using Render's database)"
echo ""

echo "🌐 Deploy to Vercel:"
echo "   1. Go to vercel.com and sign up/login"
echo "   2. Connect your GitHub account"
echo "   3. Import FlightHub-Assignment repository"
echo "   4. Set root directory to: apps/Backend"
echo "   5. Configure environment variables"
echo "   6. Deploy!"
echo ""

echo "🔧 Environment Variables to Set:"
echo "   DB_HOST=your-database-host"
echo "   DB_PORT=5432"
echo "   DB_DATABASE=flighthub"
echo "   DB_USERNAME=your-username"
echo "   DB_PASSWORD=your-password"
echo "   DB_CONNECTION=pgsql"
echo "   DB_SSLMODE=require"
echo ""

echo "✅ Benefits of Vercel:"
echo "   - Simpler deployment (no Docker)"
echo "   - Automatic environment variables"
echo "   - Built-in Laravel support"
echo "   - Global CDN"
echo "   - Free tier"
echo ""

echo "🎯 Your app will be available at:"
echo "   https://your-project-name.vercel.app"
echo ""

echo "📚 For detailed instructions, see: DEPLOY.md"
echo ""
